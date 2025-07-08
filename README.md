# Media-Associate

An application for managing, summarizing & posting news articles to WordPress. Users can upload PDFs of news articles, manage WordPress settings like categories/tags, get a concise article summary & citations using AI, then post directly to a blog.

---

## Features

-   Accessible via API
-   Dashboard to view, edit, and summarize inbound articles
-   PDF upload and parsing
-   AI-generated summaries using local or hosted models
-   Docker-ready local development environment

---

## Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/Auvenell/media-associate

    cd media-associate
    ```

2. **Install dependencies**

    ```bash
    docker compose up -d --build

    composer install

    npm install
    ```

3. **Set up environment**: edit .env and set your database credentials.

    ```bash
    cp .env.example .env

    ./vendor/bin/sail php artisan migrate
    ```

4. **Run the app**

    ```bash
    npm run dev
    ```

5. **PDF to Text Microservice**

-   PDF-to-text microservice: Ensure your microservice (e.g., Node.js-based container) is running and accessible on http://localhost:3030
-   PDF_SERVICE_URL = http://localhost:3030/api/convert
-   APP_URL = http://localhost

`This project is licensed under the MIT License.`
