# Sieu Thi Thu Cung Backend

Spring Boot backend for a pet store with layered architecture:

- Entity
- DTO
- Mapper
- Repository
- Service
- Controller

## Run

```powershell
.\mvnw.cmd spring-boot:run
```

## Test

```powershell
.\mvnw.cmd test
```

## Database

Default config uses MySQL database `sieuthithucung` in `src/main/resources/application.properties`.

## Environment Variables

Use these variables when deploying or before pushing to shared environments:

- DB_URL (optional, full JDBC URL)
- DB_HOST
- DB_PORT
- DB_NAME
- DB_USERNAME
- DB_PASSWORD
- DB_SSL_MODE
- DB_ALLOW_PUBLIC_KEY_RETRIEVAL
- DB_SERVER_TIMEZONE
- DB_URL_EXTRA_PARAMS
- JPA_DDL_AUTO
- APP_CORS_ALLOWED_ORIGIN
- APP_UPLOAD_DIR
- MULTIPART_MAX_FILE_SIZE
- MULTIPART_MAX_REQUEST_SIZE

Admin bootstrap (optional):

- APP_ADMIN_SEED_ENABLED
- APP_ADMIN_SEED_EMAIL
- APP_ADMIN_SEED_PASSWORD
- APP_ADMIN_SEED_NAME

Notes:

- Admin seed is disabled by default.
- To auto-create admin account, set APP_ADMIN_SEED_ENABLED=true and provide APP_ADMIN_SEED_EMAIL + APP_ADMIN_SEED_PASSWORD.
- Runtime uploads are ignored by git via uploads/ in .gitignore.

## Railway DB Setup

For your MySQL service (SSL REQUIRED), set Railway variables like this:

- DB_HOST=sieuthithucung-sieuthithucung.i.aivencloud.com
- DB_PORT=28896
- DB_NAME=defaultdb
- DB_USERNAME=avnadmin
- DB_PASSWORD=<your-real-password>
- DB_SSL_MODE=REQUIRED
- DB_ALLOW_PUBLIC_KEY_RETRIEVAL=true
- DB_SERVER_TIMEZONE=UTC

Then deploy/restart service on Railway.

If you prefer one variable only, set DB_URL in JDBC format:

`jdbc:mysql://sieuthithucung-sieuthithucung.i.aivencloud.com:28896/defaultdb?sslMode=REQUIRED&allowPublicKeyRetrieval=true&serverTimezone=UTC`

## SSL with ca.pem (Railway)

Java MySQL driver verifies CA via truststore (JKS/PKCS12), not raw pem path directly.

1. Put your `ca.pem` in project root (you already have `Backend/ca.pem`).
2. Create truststore from pem:

```powershell
keytool -importcert -alias mysql-ca -file ca.pem -keystore certs\mysql-truststore.jks -storepass changeit -noprompt
```

3. Set Railway variables:

- DB_SSL_MODE=VERIFY_CA
- DB_URL_EXTRA_PARAMS=&trustCertificateKeyStoreUrl=file:/app/certs/mysql-truststore.jks&trustCertificateKeyStorePassword=changeit&trustCertificateKeyStoreType=JKS

4. Keep the usual DB_HOST/DB_PORT/DB_NAME/DB_USERNAME/DB_PASSWORD values.

Alternative: set full DB_URL containing the same truststore parameters.

## Railway Runtime Fix (php: command not found)

If Railway logs show `/bin/bash: line 1: php: command not found`, your service is using an old PHP start command.

This backend is Java Spring Boot, and repo now includes:

- `nixpacks.toml` (build with Maven)
- `Procfile` (start with Java)

Expected start command:

`java -Dserver.port=$PORT -jar target/*.jar`

In Railway UI, remove old custom Start Command (if any) like `php ...`, then redeploy.

## Deploy by Dockerfile (Recommended)

This repo now includes:

- `Dockerfile` (multi-stage build: Maven -> Java runtime)
- `.dockerignore` (smaller build context)

To deploy on Railway with Docker:

1. In Railway service settings, use Dockerfile build (or leave auto-detect if Dockerfile is present).
2. Remove any custom Start Command in Railway UI.
3. Set environment variables:

- DB_HOST
- DB_PORT
- DB_NAME
- DB_USERNAME
- DB_PASSWORD
- DB_SSL_MODE (use `REQUIRED` first)
- DB_ALLOW_PUBLIC_KEY_RETRIEVAL
- DB_SERVER_TIMEZONE
- DB_URL_EXTRA_PARAMS (optional)

4. Redeploy.

For VERIFY_CA mode with your `Backend/ca.pem`:

- Dockerfile imports `ca.pem` into `/app/certs/mysql-truststore.jks` during image build.
- Set:

`DB_SSL_MODE=VERIFY_CA`

`DB_URL_EXTRA_PARAMS=&trustCertificateKeyStoreUrl=file:/app/certs/mysql-truststore.jks&trustCertificateKeyStorePassword=changeit&trustCertificateKeyStoreType=JKS`

## If Service Still Crashes

Check Railway runtime logs after redeploy. Common causes:

1. Missing runnable jar:
- Now fixed by `Procfile` and `nixpacks.toml` (auto-picks non-`*.original` jar).

2. Wrong DB variables:
- Required: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`
- For Aiven/Railway MySQL: usually `DB_SSL_MODE=REQUIRED`

3. SSL truststore path invalid (when using VERIFY_CA):
- `DB_URL_EXTRA_PARAMS` must point to an existing file in container.
- If unsure, start with `DB_SSL_MODE=REQUIRED` (without VERIFY_CA), confirm app is up, then switch to VERIFY_CA.

4. Port binding:
- App now uses `server.port=${PORT:8080}`.

## Dummy Data SQL

Dummy data file is available at `dummy-data.sql`.

Import with MySQL client:

```powershell
mysql -u root -D sieuthithucung -e "source dummy-data.sql"
```

The script uses id range `9001+` and `ON DUPLICATE KEY UPDATE`, so it can be re-run safely.

## CRUD API Pattern

Each table has REST endpoints with pattern:

- `POST /api/v1/{resource}`
- `PUT /api/v1/{resource}/{id}`
- `DELETE /api/v1/{resource}/{id}`
- `GET /api/v1/{resource}/{id}`
- `GET /api/v1/{resource}?page=0&size=10`

Implemented resources include users, roles, permissions, categories, products, product-images, cart-items, orders, order-items, order-status-history, payments, shipping-addresses, wishlists, reviews, notifications, contacts, role-permissions, and password-reset-tokens.

