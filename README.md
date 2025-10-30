# USAGE
Clone the repo
```
git clone <URL-del-repo> moviemon
cd moviemon
composer install
cp .env .env.local

```
Copy the example file
```
cp .env .env.local
```
Change the fields db_user, db_password and db_name with your database login credentials.
You can get your OMDB_API_KEY here: [https://www.omdbapi.com](https://www.omdbapi.com/apikey.aspx)
```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0"
OMDB_API_KEY=your_api_key
```
Validate and execute the migrations
```
php bin/console doctrine:schema:validate
php bin/console make:migration     
php bin/console doctrine:migrations:migrate
# or
php bin/console doctrine:schema:update --force
```
Run also
```
npm install
npm run dev
# or
yarn
yarn dev
```
Start the server
```
symfony serve
```



