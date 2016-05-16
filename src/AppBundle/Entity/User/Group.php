<?php

namespace AppBundle\Entity\User;

use AppBundle\Entity\Repository\Users;
use CoreBundle\Db\Entity;

class Group extends Entity
{
    protected static $table = 'user_groups';

    protected $users = array();
    protected static $avoidSaving = array('users');


    public function getUsers($force = false)
    {
        if (empty($this->users) || $force) {
            $usersRepository = new Users();
            $this->users = $usersRepository->findBy(
                array('group_id = ?'),
                array($this->id),
                $limit = false
            );
        }
        return $this->users;
    }

}