# Apache Virtual Host Configuration for `ezzecore1.mobi` on Port 8433

This configuration sets up a secure virtual host for `ezzecore1.mobi` on port 8433 with specific access restrictions. The document root points to `/var/www/ezzeteam/barcode-dev`.

### Configuration Details

1. **Listening Port**:

   - Port: 8433

   ```apache
   Listen 8433
   ```
2. **Virtual Host Setup**:

   - Configures `ezzecore1.mobi` and `www.ezzecore1.mobi` with the specified document root.

   ```apache
   <VirtualHost *:8433>
       ServerName ezzecore1.mobi
       ServerAlias www.ezzecore1.mobi
       DocumentRoot /var/www/ezzeteam/barcode-dev
   ```
3. **SSL Configuration**:

   - Enables SSL using Let's Encrypt certificates.

   ```apache
       # SSL configuration
       SSLEngine On
       SSLCertificateFile /etc/letsencrypt/live/ezzecore1.mobi/fullchain.pem
       SSLCertificateKeyFile /etc/letsencrypt/live/ezzecore1.mobi/privkey.pem
       Include /etc/letsencrypt/options-ssl-apache.conf
   ```
4. **Alias Configuration**:

   - Configures `/barcode-dev` to point to the document root.

   ```apache
       Alias /barcode-dev /var/www/ezzeteam/barcode-dev
   ```
5. **Directory Permissions**:

   - Sets up directory permissions for `/var/www/ezzeteam/barcode-dev` to restrict access.

   ```apache
       <Directory /var/www/ezzeteam/barcode-dev>
           Options +Indexes +FollowSymLinks
           AllowOverride All
           Require all denied
       </Directory>
   ```
6. **Specific File Access**:

   - Allows access only to `CommandHandlers.php` within `app/Handlers`.
   - Ensures that `.php` files in this directory are accessible.

   ```apache
       <Directory /var/www/ezzeteam/barcode-dev/app/Handlers>
           <Files "CommandHandlers.php">
               Require all granted
           </Files>
           # Ensure .php files are handled correctly
           <FilesMatch "\.php$">
               Require all granted
           </FilesMatch>
       </Directory>
   ```
7. **Logging**:

   - Specifies error and access logs for this virtual host.

   ```apache
       ErrorLog ${APACHE_LOG_DIR}/barcode-dev_8433_error.log
       CustomLog ${APACHE_LOG_DIR}/barcode-dev_8433_access.log combined
   ```
8. **Closing VirtualHost Block**:

   - Ends the configuration for this virtual host.

   ```apache
   </VirtualHost>
   ```
9. Complete configuration at /etc/apach2/sites-available/barcode.conf directory

```apache
 Listen 8433
<VirtualHost *:8433>
    ServerName ezzecore1.mobi
    ServerAlias www.ezzecore1.mobi
    DocumentRoot /var/www/ezzeteam/barcode-dev

    # SSL configuration
    SSLEngine On
    SSLCertificateFile /etc/letsencrypt/live/ezzecore1.mobi/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/ezzecore1.mobi/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf

    # Alias to serve /barcode-dev as the root of the DocumentRoot
    Alias /barcode-dev /var/www/ezzeteam/barcode-dev

    <Directory /var/www/ezzeteam/barcode-dev>
        Options +Indexes +FollowSymLinks
        AllowOverride All
        Require all denied
    </Directory>

    # Allow access only to CommandHandlers.php
    <Directory /var/www/ezzeteam/barcode-dev/app/Handlers>
        <Files "CommandHandlers.php">
            Require all granted
        </Files>
        # Ensure .php files are handled correctly
        <FilesMatch "\.php$">
            Require all granted
        </FilesMatch>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/barcode-dev_8433_error.log
    CustomLog ${APACHE_LOG_DIR}/barcode-dev_8433_access.log combined
</VirtualHost>
```
