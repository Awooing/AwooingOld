<?php

namespace Awoo\Auth;

use Nette\Security\Permission;

class Authorization
{
    public static function awooThePerms(): Permission
    {
        $acl = new Permission;

        $acl->addRole("guest");
        $acl->addRole("member", "guest");
        $acl->addRole("council", "member");

        $acl->addResource("news");
        $acl->addResource("comments");
        $acl->addResource("admin");

        $acl->allow("guest", ['news', 'comments'], "view");
        $acl->allow("member", ['comments'], ["create", "deleteSelf"]);
        $acl->allow("council", Permission::ALL, ['create', 'view', 'edit', 'delete', 'deleteSelf']);

        return $acl;
    }
}