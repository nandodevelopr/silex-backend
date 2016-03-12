<?php
/**
 * /src/App/Services/Rest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Services;

// Application components
use App\Entities\Base as Entity;

// Doctrine components
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

// Symfony components
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * Class Rest
 *
 * @todo How to implement COUNT functionality?
 *
 * @category    Services
 * @package     App\Services
 * @author      TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
abstract class Rest extends Base implements Interfaces\Rest
{
    /**
     * Name of the repository that current REST API will use.
     *
     * @var string
     */
    public $repositoryName;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var RecursiveValidator
     */
    protected $validator;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * Class constructor.
     *
     * @throws  \Exception
     *
     * @param   Connection         $db
     * @param   EntityManager      $entityManager
     * @param   RecursiveValidator $validator
     */
    public function __construct(Connection $db, EntityManager $entityManager, RecursiveValidator $validator)
    {
        // Store used components to service
        $this->db = $db;
        $this->entityManager = $entityManager;
        $this->validator = $validator;

        // Oh, noes this is fatal failure...
        if (is_null($this->repositoryName)) {
            throw new \Exception('You need to specify used repository... Add \'repositoryName\' property to your class');
        }
    }

    /**
     * Getter method for current repository.
     *
     * @param   bool    $force  Force entity manager fetch
     *
     * @return  EntityRepository
     */
    public function getRepository($force = false)
    {
        if ($force || !$this->repository) {
            $this->repository = $this->entityManager->getRepository($this->repositoryName);
        }

        return $this->repository;
    }

    /**
     * Generic find method to return an array of items from database. Return value is an array of specified repository
     * entities.
     *
     * @param   array           $criteria
     * @param   null|array      $orderBy
     * @param   null|integer    $limit
     * @param   null|integer    $offset
     *
     * @return  Entity[]
     */
    public function find(array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        // Before callback method call
        if (method_exists($this, 'beforeFind')) {
            $this->beforeFind($criteria, $orderBy, $limit, $offset);
        }

        // Fetch data
        $data = $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);

        // After callback method call
        if (method_exists($this, 'afterFind')) {
            $data = $this->afterFind($data, $criteria, $orderBy, $limit, $offset);
        }

        return $data;
    }

    /**
     * Generic findOne method to return single item from database. Return value is single entity from specified
     * repository.
     *
     * @param   integer $id
     *
     * @return  null|Entity
     */
    public function findOne($id)
    {
        // Before callback method call
        if (method_exists($this, 'beforeFindOne')) {
            $this->beforeFindOne($id);
        }

        $data = $this->getRepository()->find($id);

        // After callback method call
        if (method_exists($this, 'afterFindOne')) {
            $this->afterFindOne($data, $id);
        }

        return $data;
    }

    /**
     * Generic method to create new item (entity) to specified database repository. Return value is created entity for
     * specified repository.
     *
     * @throws  ValidatorException
     *
     * @param   \stdClass   $data
     *
     * @return  Entity
     */
    public function create(\stdClass $data)
    {
        // Determine entity name
        $entity = $this->getRepository()->getClassName();

        /**
         * Create new entity
         *
         * @var Entity $entity
         */
        $entity = new $entity();

        // Before callback method call
        if (method_exists($this, 'beforeCreate')) {
            $this->beforeCreate($entity, $data);
        }

        // Create or update entity
        $this->createOrUpdateEntity($entity, $data);

        // After callback method call
        if (method_exists($this, 'afterCreate')) {
            $this->afterCreate($entity, $data);
        }

        return $entity;
    }

    /**
     * Generic method to update specified entity with new data.
     *
     * @throws  HttpException
     * @throws  ValidatorException
     *
     * @param   integer     $id
     * @param   \stdClass   $data
     *
     * @return  Entity
     */
    public function update($id, \stdClass $data)
    {
        /** @var Entity $entity */
        $entity = $this->getRepository()->find($id);

        // Entity not found
        if (is_null($entity)) {
            throw new HttpException(404, 'Not found');
        }

        // Before callback method call
        if (method_exists($this, 'beforeUpdate')) {
            $this->beforeUpdate($entity, $data);
        }

        // Create or update entity
        $this->createOrUpdateEntity($entity, $data);

        // After callback method call
        if (method_exists($this, 'afterUpdate')) {
            $this->afterUpdate($entity, $data);
        }

        return $entity;
    }

    /**
     * Generic method to delete specified entity from database.
     *
     * @param   integer $id
     *
     * @return  Entity
     */
    public function delete($id)
    {
        /** @var Entity $entity */
        $entity = $this->getRepository()->find($id);

        // Entity not found
        if (is_null($entity)) {
            throw new HttpException(404, 'Not found');
        }

        // Before callback method call
        if (method_exists($this, 'beforeDelete')) {
            $this->beforeDelete($entity, $id);
        }

        // And remove entity from repo
        $this->entityManager->remove($entity);
        $this->entityManager->flush($entity);

        // After callback method call
        if (method_exists($this, 'afterDelete')) {
            $this->afterDelete($entity, $id);
        }

        return $entity;
    }

    /**
     * Before lifecycle method for find method.
     *
     * @param   array        $criteria
     * @param   null|array   $orderBy
     * @param   null|integer $limit
     * @param   null|integer $offset
     */
    public function beforeFind(array &$criteria = [], array &$orderBy = null, &$limit = null, &$offset = null) { }

    /**
     * After lifecycle method for find method.
     *
     * @param   Entity[]     $data
     * @param   array        $criteria
     * @param   null|array   $orderBy
     * @param   null|integer $limit
     * @param   null|integer $offset
     */
    public function afterFind(
        &$data = [],
        array &$criteria = [],
        array &$orderBy = null,
        &$limit = null,
        &$offset = null
    ) { }

    /**
     * Before lifecycle method for findOne method.
     *
     * @param   integer $id
     */
    public function beforeFindOne($id) { }

    /**
     * After lifecycle method for findOne method.
     *
     * @param   null|\stdClass|Entity $data
     * @param   integer               $id
     */
    public function afterFindOne(&$data, $id) { }

    /**
     * Before lifecycle method for create method.
     *
     * @param   Entity    $entity
     * @param   \stdClass $data
     */
    public function beforeCreate(Entity $entity, \stdClass $data) { }

    /**
     * After lifecycle method for create method.
     *
     * @param   Entity    $entity
     * @param   \stdClass $data
     */
    public function afterCreate(Entity $entity, \stdClass $data) { }

    /**
     * Before lifecycle method for update method.
     *
     * @param   Entity    $entity
     * @param   \stdClass $data
     */
    public function beforeUpdate(Entity $entity, \stdClass $data) { }

    /**
     * After lifecycle method for update method.
     *
     * @param   Entity    $entity
     * @param   \stdClass $data
     */
    public function afterUpdate(Entity $entity, \stdClass $data) { }

    /**
     * Before lifecycle method for delete method.
     *
     * @param   Entity  $entity
     * @param   integer $id
     */
    public function beforeDelete(Entity $entity, $id) { }

    /**
     * After lifecycle method for delete method.
     *
     * @param   Entity  $entity
     * @param   integer $id
     */
    public function afterDelete(Entity $entity, $id) { }

    /**
     * Helper method to set data to specified entity and store it to database.
     *
     * @todo    should this throw an error, if given data contains something else than entity itself?
     * @todo    should this throw an error, if setter method doesn't exists?
     *
     * @throws  ValidatorException
     *
     * @param   Entity      $entity
     * @param   \stdClass   $data
     *
     * @return  void
     */
    protected function createOrUpdateEntity(Entity $entity, \stdClass $data)
    {
        // Iterate given data
        foreach ($data as $property => $value) {
            // Specify setter method for current property
            $method = sprintf(
                'set%s',
                ucwords($property)
            );

            // Yeah method exists, so use it with current value
            if (method_exists($entity, $method)) {
                $entity->$method($value);
            }
        }

        // Validate entity
        $errors = $this->validator->validate($entity);

        // Oh noes, we have some errors
        if (count($errors) > 0) {
            throw new ValidatorException($errors);
        }

        // And make entity persist on database
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
    }
}
