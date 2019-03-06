# Minorm
Simple ORM library with error logging

### Installation
```
git clone https://github.com/Batik13/Minorm.git
```
or
```
composer require minorm/minorm:dev-master
```

### Get start:
connect namespace and create an instance of the class
```
use Minorm\Minorm;
$minorm = new Minorm();
```
**return by id**
```
$minorm->get('pages', 5);
```
**arbitrary request**
```
$minorm->query("SELECT * FROM pages WHERE url='contacts'");
```
**insert**
```
$minorm->insert('pages', [
  NULL, 'Contacts', 'contacts', 'content...'
]);
```
**update**
```
$minorm->update('pages', [
  'text' => 'new content'
], 12);
```
**delete**
```
$minorm->delete('pages', '9,10');
```
