<?php
/**
 * /src/App/Controllers/AuthorController.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Controllers;

// Application components
use App\Services\AuthorService;

// Symfony components
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidatorException;

// 3rd party components
use Swagger\Annotations as SWG;

/**
 * Class AuthorController
 *
 * @category    Controller
 * @package     App\Controllers
 * @author      TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class AuthorController extends Rest
{
    /**
     * Service that controller is using.
     *
     * @var AuthorService
     */
    protected $service;

    /**
     * Method to expose necessary services for controller use.
     *
     * @return  void
     */
    public function exposeServices()
    {
        $this->service = $this->app['author.service'];
    }

    /**
     * Method to register all routes for current controller.
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->controllers->get('/', [$this, 'find']);
        $this->controllers->get('/{id}', [$this, 'findOne']);
        $this->controllers->post('/', [$this, 'create']);
        $this->controllers->put('/{id}', [$this, 'update']);
        $this->controllers->delete('/{id}', [$this, 'delete']);
    }

    /**
     * Returns all authors from database as an array of 'AuthorEntity' objects.
     *
     * @return  \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function find()
    {
        return $this->app->json($this->service->find());
    }

    /**
     * Returns single 'AuthorEntity' object presenting given id value.
     *
     * @param   integer $id
     *
     * @return  \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function findOne($id)
    {
        $author = $this->service->findOne($id);

        if (is_null($author)) {
            throw new HttpException(404, 'Not found');
        }

        return $this->app->json($author);
    }

    /**
     * Creates new 'AuthorEntity' object to the database.
     *
     * @param   Request $request
     *
     * @return  \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create(Request $request)
    {
        $data = json_decode($request->getContent());

        try {
            $author = $this->service->create($data);
        } catch (ValidatorException $error) {
            throw new HttpException(400, $error->getMessage());
        }

        return $this->app->json($author, 201);
    }

    /**
     * Updates specified author data.
     *
     * @param   Request $request
     * @param   integer $id
     *
     * @return  \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = json_decode($request->getContent());

        try {
            $author = $this->service->update($id, $data);
        } catch (ValidatorException $error) {
            throw new HttpException(400, $error->getMessage());
        }

        return $this->app->json($author, 200);
    }

    /**
     * Method to delete specified author.
     *
     * @param   integer $id
     *
     * @return  \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function delete($id)
    {
        $this->service->delete($id);

        return $this->app->json('', 204);
    }
}
