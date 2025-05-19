# Task Management POC

This is a task management application built with Laravel. It allows users to manage tasks, upload files related to tasks, and perform basic CRUD operations. The application also integrates with an **S3 bucket** for storing files associated with tasks.


In this task management project, I primarily used the Repository Pattern to abstract data access for tasks, users, and files. This pattern helps separate the data layer from the business logic, ensuring cleaner code, centralized data handling, and easier testing. By keeping the data layers separate, it promotes maintainability and scalability, making future changes (like switching databases) simpler. Additionally, the Service Layer Pattern was used for the file uploading process. This allows for easy scalability—if the file upload functionality needs to be moved to GCP or another server in the future, we can scale the uploading service. Furthermore, this design enables the possibility of creating a dedicated microservice to handle file uploads, ensuring full scalability as the system grows.



## Requirements

- PHP >= 8.2
- Composer
- Git
- MySQL 
- Laravel 12.x

## Setup Guide

### Step 1: Clone the Repository

Clone the repository to your local machine:

```bash
git clone https://github.com/ZumryDeen/task-management-poc.git
cd task-management-poc
```

## Step 2: Install PHP Dependencies
```bash
composer install
```

## Step 3: Set Up Environment Variables
```bash
cp .env.example .env
```
Open the .env file and set your environment variables, especially the database connection details (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD), and the S3 bucket configuration for file storage.

## Step 4: Generate Application Key
```bash
php artisan key:generate
```

## Step 5: Migrate the Database and Seed the Demo User
Run the database migrations to create the necessary tables and seed the demo user for testing authentication:
```bash
php artisan migrate --seed
```
The --seed flag will run the database seeder, which will populate your database with the demo user data. The demo user will have the following credentials:

Email: demo@innodata.com
Password: 1234

You can use these credentials to authenticate and test the application.

## Step 6: Start Laravel Development Server:
Run the following command to start the development server:

```bash
php artisan serve
```

## Step 7: SRun the Queue Worker:
Since file uploads are handled asynchronously using Laravel's queue jobs, you need to start the queue worker to process the jobs (such as uploading files to S3):

```bash
php artisan queue:work
```
## Step 8: Running the test
```bash
php artisan test  
```


## How to test the REST application

1. Run the Seeder
   To populate the database with the demo user and any other necessary data, run the following command:

```bash
php artisan migrate --seed
```
This will:

Run all migrations to set up the necessary tables.

Run the database seeder to create the demo user with the following credentials:

Email: demo@innodata.com

Password: 1234

### 2. Get Authentication Token
   Now that you have the demo user, you need to authenticate and get an Auth Token.

Run the following curl command to log in with the demo user credentials:

```bash
curl --location 'http://127.0.0.1:8000/api/login' \
--header 'Content-Type: application/json' \
--data-raw '{
"email": "demo@test.com",
"password": "1234"
}'
```
This will return a response with an auth token that looks like this:

```bash
{
"token": "7|4w38CjktUS4As5TCXEZAEmrk6nOU8oLxjoIcDG55b4e37f42"
}
```
Copy the token from the response; you'll need it for the next steps.

curl --location 'http://127.0.0.1:8000/api/tasks' \
--header 'Authorization: Bearer 4|n8OI84FiTTnRzRcbL6XBkDFYkAOzGBGPGwGwrgZH7c613a13'


1. Create Task with File Upload (POST request):

```bash
curl --location --request POST 'http://127.0.0.1:8000/api/tasks' \
--header 'Authorization: Bearer <YOUR_TOKEN>' \
--header 'Accept: application/json' \
--form 'name="New Task Name"' \
--form 'description="New task description."' \
--form 'status="pending"' \
--form 'files[]=@"/path/to/file"'
```


2. Update Task (PUT request):

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/tasks/1' \
--header 'Authorization: Bearer <YOUR_TOKEN>' \
--header 'Accept: application/json' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'name=Updated Task Name' \
--data-urlencode 'description=Updated task description.' \
--data-urlencode 'status=in-progress'
```
3. Get Task (GET request):

```bash
curl --location --request GET 'http://127.0.0.1:8000/api/tasks/1' \
--header 'Authorization: Bearer <YOUR_TOKEN>' \
--header 'Accept: application/json'
```
4 List Task (GET request):
```bash
curl --location --request GET 'http://127.0.0.1:8000/api/tasks' \
--header 'Authorization: Bearer <YOUR_TOKEN>' \
--header 'Accept: application/json'
```

5 .Delete Task (DELETE request):

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/tasks/3' \
--header 'Authorization: Bearer <YOUR_TOKEN>' \
--header 'Accept: application/json'
```



