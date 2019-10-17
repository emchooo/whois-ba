# WHOIS domain lookup for .BA domains through terminal

## What is this?

This is fun open-source side project for checking .BA domain through terminal. Response of WHOIS lookup on official website [NIC.ba](nic.ba) is returned as image, so it is not possible to scrape WHOIS data for .BA domains. So here we use OCR software to convert WHOIS data from image to text.

## How to use?

Ping whois.div.ba/{domain}

Example

```
curl whois.div.ba/nic.ba
```

## What we use?
- [Lumen](https://lumen.laravel.com/) - PHP micro-framework
- [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) - OCR Engine
- [Tesseract OCR for PHP](https://github.com/thiagoalessio/tesseract-ocr-for-php) - Tesseract OCR PHP wrapper