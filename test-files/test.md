# Getting Started with PortalPress

PortalPress is a lightweight client portal plugin for WordPress. This guide covers everything you need to get up and running.

## Requirements

- PHP 8.2 or higher
- WordPress 6.9 or higher
- A compatible theme (any block or classic theme)

## Installation

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for **"PortalPress"** and click **Install Now**
3. Click **Activate**

Alternatively, download the plugin from [wordpress.org](https://wordpress.org/plugins/portalpress/) and upload the `.zip` file via **Plugins > Add New > Upload Plugin**.

## Initial Setup

### Set Up Your Pages

Go to **PortalPress > Settings > General** and assign pages for:

- **Portal Page** — the main client portal (e.g. `/portal/`)
- **Login Page** — where clients log in (e.g. `/portal/login/`)
- **Register Page** — client self-registration (e.g. `/portal/register/`)

### Configure Registration

Go to **PortalPress > Settings > Registration** to enable or disable client self-registration. When enabled, clients can register at your registration page and will automatically be marked as portal users.

### Add Your First Client

There are two ways to add clients:

**From WordPress Users:**

1. Go to **Users > Add New**
2. Fill in user details
3. Check **"Make this user a PortalPress Client"**
4. Optionally check **"Send Welcome Email"**

**Client Self-Registration:**

If enabled, clients can register at your registration page. They'll be automatically marked as clients and receive a welcome email.

## Configuration Options

Here's a summary of available settings:

| Setting | Default | Description |
|---------|---------|-------------|
| Portal Page | None | Main portal landing page |
| Login Page | None | Custom login page for clients |
| Registration | Disabled | Allow client self-registration |
| Welcome Email | Enabled | Send email on new client creation |
| File Uploads | 10 MB | Maximum upload size per file |

## Code Examples

To check if the current user is a PortalPress client in your theme:

```php
if ( function_exists( 'pp_is_client' ) && pp_is_client() ) {
    echo 'Welcome back, client!';
}
```

You can also retrieve client data programmatically:

```php
$client = pp_get_client( $user_id );

if ( $client ) {
    echo $client->get_name();
    echo $client->get_company();
}
```

## Shortcodes

PortalPress provides several shortcodes:

- `[pp_portal]` — Renders the full portal interface
- `[pp_login]` — Renders the login form
- `[pp_register]` — Renders the registration form
- `[pp_files]` — Displays the client's uploaded files

### Shortcode Example

```html
<div class="my-portal-wrapper">
    [pp_portal]
</div>
```

## Frequently Asked Questions

### Can I customize the portal appearance?

Yes! PortalPress uses standard WordPress CSS classes. You can style everything via your theme's `style.css` or the Customizer's **Additional CSS** section.

### Does it work with page builders?

PortalPress works with any page builder that supports shortcodes, including:

- Elementor
- Beaver Builder
- Divi
- WPBakery

### How do I migrate from another portal plugin?

Currently there's no automated migration tool. You'll need to:

1. Export your data from the old plugin
2. Import users via **Tools > Import**
3. Reassign them as PortalPress clients
4. Re-upload any client files

> **Note:** Always back up your database before migrating between plugins.

## Troubleshooting

### Portal page shows a 404

This usually means permalink rules need to be flushed:

1. Go to **Settings > Permalinks**
2. Click **Save Changes** (no need to change anything)
3. Try accessing the portal page again

### Emails not sending

Check that your WordPress site can send emails. We recommend using an SMTP plugin like **WP Mail SMTP** for reliable email delivery.

---

That's it! You're ready to start using PortalPress. For more help, visit our [documentation](https://docs.portalpress.com) or reach out on the [support forum](https://wordpress.org/support/plugin/portalpress/).
