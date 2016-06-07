# plus GainTime
###### Versão de desenvolvimento

**Abstração de CRUD em php para o GainTime**


### Model
Você vai utilizar a `./lib/model/base.php` para dar um `extends` da nossa `\Model\Base` em sua model.

```php
require_once('../lib/model/base.php');

class User extends \Model\Base
{
    public $fillable = ['id', 'name', 'email', 'level'];
}

```

Isto é tudo o que você precisa para utilizar as funções básicas de um CRUD.

IMPORTANTE: Para utilizar a `\Model\Base`, suas funções e propriedades corretamente, você vai precisar nomear suas tabelas do banco de dados com o mesmo nome da model correpondente acrescido de s.
```
- Model: User
- Correspondent database table: users
```

### Configurações

Para configurar as credenciais para acesso ao mysql crie um arquivo `.env` e copie o conteúdo do arquivo `.env.sample` alterando para os valores corretos do ambiente no qual está configurando, o arquivo tem a seguinte estrutura:

```
APP_URL=http://localhost

DB_CONNECTION=<bd_type>
DB_HOST=<bd_server>
DB_NAME=<db_name>
DB_USER=<db_user (duh!)>
DB_PASSWORD=<what_do_u_thnk_ll_be_placed_here?>
```
... we'll continue
