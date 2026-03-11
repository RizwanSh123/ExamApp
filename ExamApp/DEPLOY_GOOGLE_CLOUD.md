# Deploy ExamApp on Google Cloud (Cloud Run + Cloud SQL + Domain)

## 1. What is added
- PHP API: `/api/state.php` (stores full app state in MySQL)
- Health check: `/api/health.php`
- MySQL schema: `sql/schema.sql`
- Container runtime: `Dockerfile`, `docker/start.sh`
- Frontend cloud sync: `examportal-v6.html` now pulls/pushes state to `/api/state.php`

## 2. Prerequisites
- Google Cloud project
- Billing enabled
- Your institute domain managed in DNS
- `gcloud` CLI installed and logged in

## 3. Create Cloud SQL (MySQL)
```bash
gcloud services enable sqladmin.googleapis.com run.googleapis.com cloudbuild.googleapis.com

gcloud sql instances create examapp-mysql \
  --database-version=MYSQL_8_0 \
  --tier=db-f1-micro \
  --region=asia-south1

gcloud sql databases create exam_portal --instance=examapp-mysql
gcloud sql users create exam_user --instance=examapp-mysql --password=CHANGE_ME_STRONG
```

Import schema:
```bash
gcloud sql connect examapp-mysql --user=root
```
Then run SQL from `sql/schema.sql`.

## 4. Build and deploy Cloud Run
Set these values first:
- `PROJECT_ID`
- `REGION` (example: `asia-south1`)
- `INSTANCE_CONN` = `PROJECT_ID:REGION:examapp-mysql`

```bash
gcloud config set project PROJECT_ID

gcloud builds submit --tag gcr.io/PROJECT_ID/examapp

gcloud run deploy examapp \
  --image gcr.io/PROJECT_ID/examapp \
  --region REGION \
  --allow-unauthenticated \
  --add-cloudsql-instances INSTANCE_CONN \
  --set-env-vars DB_SOCKET=/cloudsql/INSTANCE_CONN,DB_NAME=exam_portal,DB_USER=exam_user,DB_PASS=CHANGE_ME_STRONG
```

## 5. Verify
- Open service URL from Cloud Run
- Check:
  - `/api/health.php`
  - `/api/state.php`

## 6. Connect your institute domain
```bash
gcloud domains verify yourdomain.edu

gcloud beta run domain-mappings create \
  --service examapp \
  --domain yourdomain.edu \
  --region REGION
```

Google Cloud will show required DNS records. Add those in your domain DNS panel.

## 7. Important security hardening (next step)
- Add API authentication to `/api/state.php` (token/JWT/session)
- Restrict CORS in `api/utils.php` to your domain only
- Use Secret Manager for DB password instead of plain env value
