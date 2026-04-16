<?php
/**
 * Markdown → Gutenberg block markup.
 *
 * Mirrors the output of the front-end sidebar import (marked.js + wp.blocks.rawHandler)
 * closely enough that docs imported via either path look the same.
 *
 * @package MVP_Docs
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Parsedown' ) ) {
	require_once MVPD_PATH . 'includes/vendor/Parsedown.php';
}

/**
 * Convert Markdown to serialized Gutenberg block markup.
 *
 * @param string $markdown Raw Markdown source.
 * @return array { 'title' => string, 'content' => string }
 */
function mvpd_markdown_to_blocks( $markdown ) {
	$parser = new Parsedown();
	$parser->setBreaksEnabled( false );
	$html = $parser->text( (string) $markdown );

	$title = '';
	if ( preg_match( '#^\s*<h1[^>]*>(.*?)</h1>\s*#is', $html, $m ) ) {
		$title = trim( wp_strip_all_tags( $m[1] ) );
		$html  = preg_replace( '#^\s*<h1[^>]*>.*?</h1>\s*#is', '', $html, 1 );
	}

	$content = mvpd_walk_html_to_blocks( $html );

	return [ 'title' => $title, 'content' => $content ];
}

/**
 * Walk Parsedown HTML and emit block markup.
 */
function mvpd_walk_html_to_blocks( $html ) {
	$html = trim( $html );
	if ( '' === $html ) {
		return '';
	}

	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$wrapped = '<?xml encoding="UTF-8"?><root>' . $html . '</root>';
	$doc->loadHTML( $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();

	$root = $doc->getElementsByTagName( 'root' )->item( 0 );
	if ( ! $root ) {
		return '';
	}

	$blocks = [];
	foreach ( iterator_to_array( $root->childNodes ) as $node ) {
		$block = mvpd_node_to_block( $node, $doc );
		if ( null !== $block && '' !== $block ) {
			$blocks[] = $block;
		}
	}

	return implode( "\n\n", $blocks );
}

/**
 * Convert a single top-level DOM node to a block comment string.
 */
function mvpd_node_to_block( DOMNode $node, DOMDocument $doc ) {
	if ( XML_TEXT_NODE === $node->nodeType ) {
		return '';
	}
	if ( XML_ELEMENT_NODE !== $node->nodeType ) {
		return '';
	}

	$tag = strtolower( $node->nodeName );

	switch ( $tag ) {
		case 'h1':
		case 'h2':
		case 'h3':
		case 'h4':
		case 'h5':
		case 'h6':
			return mvpd_block_heading( $node, (int) substr( $tag, 1 ) );

		case 'p':
			$inner = mvpd_inner_html( $node );
			if ( '' === trim( $inner ) ) {
				return '';
			}
			return "<!-- wp:paragraph -->\n<p>{$inner}</p>\n<!-- /wp:paragraph -->";

		case 'ul':
			return mvpd_block_list( $node, false );

		case 'ol':
			return mvpd_block_list( $node, true );

		case 'pre':
			return mvpd_block_code( $node );

		case 'blockquote':
			return mvpd_block_quote( $node, $doc );

		case 'hr':
			return "<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->";

		case 'table':
			return mvpd_block_table( $node );

		case 'img':
			return mvpd_block_image( $node );

		default:
			// Unknown top-level element — emit as HTML block for fidelity.
			$out = $doc->saveHTML( $node );
			return "<!-- wp:html -->\n{$out}\n<!-- /wp:html -->";
	}
}

function mvpd_block_heading( DOMElement $node, $level ) {
	$inner = mvpd_inner_html( $node );
	$attrs = ( 2 === $level ) ? '' : ' ' . wp_json_encode( [ 'level' => $level ] );
	return "<!-- wp:heading{$attrs} -->\n<h{$level} class=\"wp-block-heading\">{$inner}</h{$level}>\n<!-- /wp:heading -->";
}

function mvpd_block_list( DOMElement $node, $ordered ) {
	$items = [];
	foreach ( $node->childNodes as $child ) {
		if ( XML_ELEMENT_NODE === $child->nodeType && 'li' === strtolower( $child->nodeName ) ) {
			$items[] = mvpd_block_list_item( $child );
		}
	}

	$attrs  = $ordered ? ' {"ordered":true}' : '';
	$tag    = $ordered ? 'ol' : 'ul';
	$joined = implode( "\n\n", $items );

	return "<!-- wp:list{$attrs} -->\n<{$tag} class=\"wp-block-list\">{$joined}</{$tag}>\n<!-- /wp:list -->";
}

function mvpd_block_list_item( DOMElement $li ) {
	$inner = trim( mvpd_inner_html( $li ) );
	// Parsedown wraps loose list content in <p>; strip a single outer <p>.
	if ( preg_match( '#^<p>(.*)</p>$#is', $inner, $m ) ) {
		$inner = $m[1];
	}
	// rawHandler serializes list-item text content without escaping >.
	$inner = str_replace( '&gt;', '>', $inner );
	return "<!-- wp:list-item -->\n<li>{$inner}</li>\n<!-- /wp:list-item -->";
}

function mvpd_block_code( DOMElement $pre ) {
	// Parsedown emits <pre><code class="language-X">...</code></pre>.
	$code_node = null;
	foreach ( $pre->childNodes as $c ) {
		if ( XML_ELEMENT_NODE === $c->nodeType && 'code' === strtolower( $c->nodeName ) ) {
			$code_node = $c;
			break;
		}
	}
	$content = $code_node ? mvpd_inner_html( $code_node ) : mvpd_inner_html( $pre );

	// Match WordPress's stored form: un-escape > and escape [ as &#91;.
	$content = str_replace( [ '&gt;', '[' ], [ '>', '&#91;' ], $content );

	return "<!-- wp:code -->\n<pre class=\"wp-block-code\"><code>{$content}</code></pre>\n<!-- /wp:code -->";
}

function mvpd_block_quote( DOMElement $bq, DOMDocument $doc ) {
	$inner_blocks = [];
	foreach ( iterator_to_array( $bq->childNodes ) as $child ) {
		$b = mvpd_node_to_block( $child, $doc );
		if ( null !== $b && '' !== $b ) {
			$inner_blocks[] = $b;
		}
	}
	$joined = implode( "\n\n", $inner_blocks );
	return "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\">{$joined}</blockquote>\n<!-- /wp:quote -->";
}

function mvpd_block_table( DOMElement $table ) {
	// Re-serialize the table, force class="has-fixed-layout", wrap in figure.
	$xpath = new DOMXPath( $table->ownerDocument );

	// Build a copy to set class on.
	$clone = $table->cloneNode( true );
	$clone->setAttribute( 'class', 'has-fixed-layout' );

	$inner = $clone->ownerDocument->saveHTML( $clone );
	// saveHTML on a cloned-but-not-inserted node can fail on some libxml builds; fall back:
	if ( ! $inner ) {
		$inner = $table->ownerDocument->saveHTML( $table );
		$inner = preg_replace( '#<table\b[^>]*>#i', '<table class="has-fixed-layout">', $inner, 1 );
	}

	// Collapse inter-tag whitespace to match rawHandler's table serialization.
	$inner = preg_replace( '/>\s+</', '><', $inner );

	return "<!-- wp:table -->\n<figure class=\"wp-block-table\">{$inner}</figure>\n<!-- /wp:table -->";
}

function mvpd_block_image( DOMElement $img ) {
	$src = $img->getAttribute( 'src' );
	$alt = $img->getAttribute( 'alt' );
	$out = sprintf( '<figure class="wp-block-image"><img src="%s" alt="%s"/></figure>', esc_attr( $src ), esc_attr( $alt ) );
	return "<!-- wp:image -->\n{$out}\n<!-- /wp:image -->";
}

/**
 * Serialize a node's children as HTML (no outer tag).
 */
function mvpd_inner_html( DOMNode $node ) {
	$html = '';
	foreach ( $node->childNodes as $child ) {
		$html .= $node->ownerDocument->saveHTML( $child );
	}
	return $html;
}
