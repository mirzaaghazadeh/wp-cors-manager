# CORS Manager WordPress Plugin

A comprehensive WordPress plugin for managing Cross-Origin Resource Sharing (CORS) settings. This plugin allows administrators to easily configure CORS headers and manage allowed origins through a user-friendly interface in the WordPress admin panel.

## Features

- **Easy CORS Configuration**: Simple toggle to enable/disable CORS
- **Origin Management**: Add multiple allowed origins with validation
- **Method Control**: Select which HTTP methods to allow (GET, POST, PUT, DELETE, etc.)
- **Header Management**: Configure allowed headers for cross-origin requests
- **Credentials Support**: Option to allow credentials in CORS requests
- **Security Focused**: Built with security best practices in mind
- **User-Friendly Interface**: Clean, intuitive admin interface under Tools menu
- **Real-time Status**: View current CORS configuration at a glance
- **Help Documentation**: Built-in help and security guidelines

## Installation

1. Upload the `HEADERS` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Tools > CORS Manager** in your WordPress admin panel
4. Configure your CORS settings as needed

## Configuration

### Basic Setup

1. **Enable CORS**: Check the "Enable CORS" checkbox to activate CORS headers
2. **Add Allowed Origins**: Enter the domains that should be allowed to make cross-origin requests
3. **Select Methods**: Choose which HTTP methods to allow
4. **Configure Headers**: Specify which headers are allowed in requests
5. **Save Settings**: Click "Save Settings" to apply your configuration

### Allowed Origins

Enter one origin per line in the format:
```
https://example.com
https://app.example.com
https://subdomain.example.org
```

**Security Note**: Never use `*` (wildcard) for allowed origins in production environments.

### HTTP Methods

Select from the following methods:
- GET
- POST
- PUT
- DELETE
- OPTIONS
- PATCH
- HEAD

### Headers

Common headers to allow:
```
Content-Type, Authorization, X-Requested-With, Accept, Origin
```

## Security Considerations

- **Never use wildcard origins (`*`) in production**
- **Only allow trusted domains** that need access to your API
- **Be cautious with credentials** when using multiple origins
- **Regularly review** your allowed origins list
- **Test thoroughly** before deploying to production

## API Usage

Once configured, your WordPress site will automatically send appropriate CORS headers for:

- WordPress REST API endpoints
- AJAX requests
- Custom API endpoints
- File uploads

## Troubleshooting

### Common Issues

**CORS errors still occurring:**
- Verify the plugin is activated
- Check that CORS is enabled in settings
- Ensure the requesting origin is in your allowed origins list
- Verify the HTTP method is allowed

**Plugin not working:**
- Check for plugin conflicts
- Verify WordPress version compatibility
- Review server error logs

**Headers not appearing:**
- Ensure no other plugins are overriding headers
- Check server configuration
- Verify .htaccess rules aren't conflicting

### Debug Mode

To debug CORS issues:

1. Open browser developer tools
2. Check the Network tab for CORS headers
3. Look for preflight OPTIONS requests
4. Verify response headers match your configuration

## Technical Details

### How It Works

The plugin works by:

1. **Intercepting requests**: Hooks into WordPress request lifecycle
2. **Checking origins**: Validates requesting origin against allowed list
3. **Adding headers**: Sends appropriate CORS headers in response
4. **Handling preflight**: Manages OPTIONS requests for complex CORS requests

### Hooks and Filters

The plugin uses these WordPress hooks:
- `init` - Initialize plugin
- `admin_menu` - Add admin menu item
- `wp_loaded` - Handle CORS logic
- `wp_headers` - Add headers to responses

### Database Storage

Settings are stored in the WordPress options table as `cors_manager_options`.

## Requirements

- WordPress 4.7 or higher
- PHP 7.0 or higher
- Administrator privileges to configure

## Changelog

### Version 1.0.0
- Initial release
- Basic CORS configuration
- Origin management
- Method and header control
- Admin interface
- Security features

## Support

For support and feature requests, please contact the plugin developer.

## License

This plugin is licensed under the GPL v2 or later.

---

**Note**: Always test CORS configuration in a development environment before deploying to production. Incorrect CORS settings can break functionality or create security vulnerabilities.