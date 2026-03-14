Recommended PHP upload configuration for this project

This project supports uploading images and videos. Many systems use conservative PHP defaults (e.g. upload_max_filesize=2M, post_max_size=8M) which will reject larger uploads and produce server-side warnings.

Recommended conservative values for development/test servers (adjust for production carefully):

- upload_max_filesize = 100M
- post_max_size = 110M
- memory_limit = 512M

Notes:
- `post_max_size` should be >= `upload_max_filesize` to allow the POST body to contain the file(s) and other form fields.
- `memory_limit` should be large enough to handle file processing if your code reads full file contents into memory; prefer streaming where possible.
- These are global PHP settings and can be applied temporarily when launching the built-in PHP server using `-d` flags (Windows PowerShell example):

```powershell
D:\php\php -d upload_max_filesize=100M -d post_max_size=110M -d memory_limit=512M -S 0.0.0.0:8089 -t .
```

- To make changes permanent, edit your `php.ini` and restart your web server. Look for the following directives in `php.ini`:

```
upload_max_filesize = 100M
post_max_size = 110M
memory_limit = 512M
```

Security and production considerations:
- Larger upload limits increase the risk of resource exhaustion and denial-of-service. Use conservative limits in production and prefer direct-to-cloud uploads or chunked uploads for very large files.
- Validate uploaded file types and sizes server-side (the project already checks common extensions).
- Ensure the `uploads/` directory is not web-executable (store outside webroot or restrict execution via webserver config) and has appropriate permissions.

If you'd like, I can update the repository README or add a short shell/powershell script to start the dev server with these flags.
