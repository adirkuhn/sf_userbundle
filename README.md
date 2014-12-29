UserBundle
----------

Bundle para Symfony2 de gerenciamento de usuários. Fornece apenas endpoints REST para gerenciamento dos usuáios (CRUD)

Instalação
----------

Setar o encoder no arquivo security.yml

security:
    encoders:
        AdirKuhn\UserBundle\Entity\User: sha512
---

 * /user/{id}
    {id} int Identificação do usuário

    retorna as informações do usuários

 * /user/save
    [POST]

    Salva usuário


 * /users

    Retorna todos os usuários do sistema.
    Páginação em /users/2, /users/3 ...


https://github.com/calinrada/php-apidoc