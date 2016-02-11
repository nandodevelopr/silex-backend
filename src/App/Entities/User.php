<?php
/**
 * /src/App/Entities/User.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Entities;

// Native components
use JsonSerializable;

// Symfony components
use Symfony\Component\Security\Core\Role\Role;

// Doctrine components
use Doctrine\ORM\Mapping as ORM;

// 3rd party components
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Swagger\Annotations as SWG;

/**
 * Class User
 *
 * @SWG\Definition(
 *      title="User",
 *      description="User data as in JSON object",
 *      type="object",
 *      required={
 *          "username",
 *          "firstname",
 *          "surname",
 *          "email",
 *      },
 *      example={
 *          "id": 1,
 *          "username": "admin",
 *          "firstname": "Arnold",
 *          "surname": "Administrator",
 *          "email": "arnold@foobar.com",
 *      },
 * )
 *
 * @ORM\Table(
 *      name="user",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="uq_username",
 *              columns={"username"}
 *          ),
 *          @ORM\UniqueConstraint(
 *              name="uq_email",
 *              columns={"email"}
 *          ),
 *      },
 *  )
 * @ORM\Entity
 *
 * @package App\Entities
 */
class User extends Base implements AdvancedUserInterface, JsonSerializable
{
    /**
     * User ID
     *
     * @var integer
     *
     * @SWG\Property()
     * @ORM\Column(
     *      name="id",
     *      type="integer",
     *      nullable=false
     *  )
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Username
     *
     * @var string
     *
     * @SWG\Property()
     * @ORM\Column(
     *      name="username",
     *      type="string",
     *      length=255,
     *      nullable=false
     *  )
     */
    private $username;

    /**
     * Firstname
     *
     * @var string
     *
     * @SWG\Property()
     * @ORM\Column(
     *      name="firstname",
     *      type="string",
     *      length=255,
     *      nullable=false
     *  )
     */
    private $firstname;

    /**
     * Surname
     *
     * @var string
     *
     * @SWG\Property()
     * @ORM\Column(
     *      name="surname",
     *      type="string",
     *      length=255,
     *      nullable=false
     *  )
     */
    private $surname;

    /**
     * Email address
     *
     * @var string
     *
     * @SWG\Property()
     * @ORM\Column(
     *      name="email",
     *      type="string",
     *      length=255,
     *      nullable=false
     *  )
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="password",
     *      type="string",
     *      length=255,
     *      nullable=false
     *  )
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="roles",
     *      type="string",
     *      length=255,
     *      nullable=false
     *  )
     */
    private $roles;

    /**
     * Getter method for current user ID.
     *
     * @return  integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        if (!is_array($this->roles)) {
            $this->roles = explode(',', $this->roles);
        }

        $roles = $this->roles;

        // Every user must have at least one role, per Silex security docs.
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * Method to verify given password against hashed one.
     *
     * @param   string  $password
     *
     * @return  bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        // TODO: Implement isAccountNonExpired() method.
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        // TODO: Implement isAccountNonLocked() method.
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        // TODO: Implement isCredentialsNonExpired() method.
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        // TODO: Implement isEnabled() method.
        return true;
    }

    /**
     * Getter method for user identifier, this can be username or email.
     *
     * @todo    How to determine which one this is?
     *
     * @return  string
     */
    public function getIdentifier()
    {
        return $this->username;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return  array   data which can be serialized by <b>json_encode</b>, which is a value of any type other than a
     *                  resource.
     */
    public function jsonSerialize()
    {
        return array(
            'id'        => $this->id,
            'username'  => $this->username,
            'firstname' => $this->firstname,
            'surname'   => $this->surname,
            'email'     => $this->email,
            'roles'     => $this->getRoles(),
        );
    }
}
