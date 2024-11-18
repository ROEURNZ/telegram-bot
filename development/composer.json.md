# Composer Configuration for `app/barcode-dev`

This `composer.json` file sets up a PHP-based Telegram bot library that interacts with the Telegram API to provide automated responses and various functionalities.

### Configuration Details

- **Name**: `app/barcode-dev`
- **Description**: A PHP library for a Telegram bot that automates interactions and responses.
- **Type**: Library
- **Minimum Stability**: Stable
- **License**: MIT

### Requirements

- **PHP**: Supports versions `^8.2` and `^8.3.10`.
- **Dependencies**:
  - `vlucas/phpdotenv`: Version `^5.6.1`, used for managing environment variables.
  - `thiagoalessio/tesseract_ocr`: Version `^2.13`, integrates OCR functionality for text recognition within images.

### Autoloading

- **PSR-4 Autoloading**: Configured for the namespace `App\BarcodeDev\`, mapping it to the `app/` directory.
  ```json
  "autoload": {
      "psr-4": {
          "App\\BarcodeDev\\": "app/"
      }
  }
  ```

### Author Information

- **Author**: ROEURN
  - **Homepage**: [GitHub - ROEURNZ](https://github.com/ROEURNZ)

### Additional Configurations

- **Allow Plugins**:
  - `php-http/discovery` is enabled to allow automatic HTTP client discovery.

```json
"config": {
    "allow-plugins": {
        "php-http/discovery": true
    }
}
```
