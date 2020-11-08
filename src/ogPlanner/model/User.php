<?php

namespace ogPlanner\model;

require_once 'IUser.php';


/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="users") // Todo: Add repo annotation
 */
class User implements IUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    protected int $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $name; // Todo: Nullable?

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $email;

    public function __construct($id = false, $email = false, $name = false)
    {
        if ($id) {
            $this->id = $id;
            $this->email = $email;
            $this->name = $name;
        }
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }
}