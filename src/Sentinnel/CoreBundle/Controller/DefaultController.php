<?php

namespace Sentinnel\CoreBundle\Controller;

use Sentinnel\CoreBundle\Manager\ProfilerManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Profiler\FileProfilerStorage;

/**
 * Class DefaultController
 * @package Sentinnel\CoreBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="profile_list")
     */
    public function indexAction()
    {
        $dir = '/home/cinfante/htdocs/www/buses.taller/app/cache/prod/profiler';

        $profiler = new ProfilerManager($dir);
        $values = $profiler->findAll();

        return $this->render('@SentinnelCore/Default/index.html.twig', array(
            'profiles' => $values,
        ));
    }

    /**
     * @param $token
     *
     * @Route("/{token}", name="profile_show")
     */
    public function showAction($token)
    {
        $dir = '/home/cinfante/htdocs/www/buses.taller/app/cache/prod/profiler';

        $profiler = new ProfilerManager($dir);
        $profile = $profiler->read($token);

        return $this->render('@SentinnelCore/Default/show.html.twig', array(
            'profile' => $profile,
        ));
    }
}
