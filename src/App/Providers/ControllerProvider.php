<?php
/**
 * /src/App/Providers/ControllerProvider.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Providers;

// Application components
use App\Application;
use App\Controllers;

// Silex components
use Silex\Application as App;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

// 3rd party components
use phpDocumentor\Reflection\DocBlockFactory;

/**
 * Class ControllerProvider
 *
 * @category    Provider
 * @package     App\Providers
 * @author      TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class ControllerProvider implements ControllerProviderInterface
{
    /**
     * Current Silex application
     *
     * @var Application
     */
    private $app;

    /**
     * Returns routes to connect to the given application.
     *
     * @param   App $app    An Application instance
     *
     * @return  ControllerCollection    A ControllerCollection instance
     */
    public function connect(App $app)
    {
        // Store application
        $this->app = $app;

        // Set error handling globally
        $this->app->error([$this, 'error']);

        /**
         * Get application current controllers
         *
         * @var ControllerCollection $controllers
         */
        $controllers = $this->app['controllers_factory'];

        // Mount controllers to specified routes.
        $this->mount();

        return $controllers;
    }

    /**
     * Generic error handler for application. Note that this will _not_ catch PHP errors, those are handled via
     * App\Core\ExceptionHandler class which extends base Symfony ExceptionHandler class.
     *
     * @param   \Exception  $exception
     * @param   integer     $status
     *
     * @return  \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function error(\Exception $exception, $status)
    {
        // Basic error data
        $error = [
            'message'   => $exception->getMessage(),
            'status'    => $status,
            'code'      => $exception->getCode(),
        ];

        // If we're running application in debug mode, attach some extra information about actual error
        if ($this->app['debug']) {
            $error += [
                'debug' => [
                    'file'          => $exception->getFile(),
                    'line'          => $exception->getLine(),
                    'trace'         => $exception->getTrace(),
                    'traceString'   => $exception->getTraceAsString(),
                ]
            ];
        }

        // And return JSON output
        return $this->app->json($error, $status);
    }

    /**
     * Attach application mount points to specified controllers.
     *
     * @return  void
     */
    private function mount()
    {
        foreach ($this->getMountPoints() as $mount) {
            $this->app->mount($mount->mountPoint, new $mount->controller);
        }
    }

    /**
     * Getter for mount points and actual controller classes for those.
     *
     * @todo Build a cache for this!
     *
     * @return  array
     */
    private function getMountPoints()
    {
        // Create doc block factory
        $factory = DocBlockFactory::createInstance();

        /**
         * Lambda function to iterate all controller classes to return mount points to each of them.
         *
         * @param   string  $file
         *
         * @return  null|\stdClass
         */
        $iterator = function($file) use ($factory) {
            // Specify controller class name with namespace
            $className = '\\App\\Controllers\\' . str_replace('.php', '', basename($file));

            // Get reflection about controller class
            $reflectionClass = new \ReflectionClass($className);

            // We're not interested about abstract classes
            if ($reflectionClass->isAbstract()) {
               return null;
            }

            // Get 'mountPoint' tags from class comment block
            $tags = $factory->create($reflectionClass->getDocComment())->getTagsByName('mountPoint');

            // Nice, we have one 'mountPoint' tag in comments, so we'll use that
            if (count($tags) === 1) {
                $tag = $tags[0];

                // Normalize mount point name
                $mountPoint = trim(str_replace('@' . $tag->getName(), '', $tag->render()));

                // Create output
                $output = new \stdClass();
                $output->mountPoint = $mountPoint;
                $output->controller = $className;

                return $output;
            }

            return null;
        };

        return array_filter(array_map($iterator, glob($this->app->getRootDir() . 'src/App/Controllers/*.php')));
    }
}
