# Refactors list
This file list the refactores needed, related with a specific date.

# 2018-07-28
Refactor: Construtor of validators has inverterd parameters
Impact: small
Tipe: manual
Motivation: integrate \Validator\Validator and \Type\Generic that became one thing.

# 2019-02-27
Blend has an initial "module" support. To use it you need to change httaccess. Like the example above.

And you need to activate it in config.

```
$config['use-module'] = true;
```


```
php_flag display_errors 1
AddDefaultCharset UTF-8
DefaultLanguage pt-BR
Options -Indexes
IndexIgnore *

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)\/(.+)?    index.php?m=$1&p=$2&e=$3&v=$4 [QSA,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)\/(.+)?    index.php?m=$1&p=$2&e=$3 [QSA,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)    index.php?m=$1&p=$2 [QSA,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9_-]+)    index.php?p=$1 [QSA,L]
```
