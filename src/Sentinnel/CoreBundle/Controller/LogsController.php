<?php

namespace Sentinnel\CoreBundle\Controller;

use Sentinnel\CoreBundle\Manager\LogsManager;
use Sentinnel\CoreBundle\Manager\ProfilerManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Profiler\FileProfilerStorage;

/**
 * Class LogsController
 * @package Sentinnel\CoreBundle\Controller
 *
 * @Route("/logs")
 */
class LogsController extends Controller
{
    /**
     * @Route("/", name="logs_list")
     */
    public function indexAction()
    {
        $systemDir = '/home/cinfante/htdocs/www/buses.taller';
        $dir = $systemDir . '/app/logs/prod.log';

        $logsManager = new LogsManager();

        $logs = $logsManager->findAll($dir);
//        $grep = preg_grep("#^\[2015-06-07 11:30:17\]#", $logs);

        $grep = preg_grep("#ERROR#", $logs);
        var_dump($grep);die;
        return $this->render('@SentinnelCore/Logs/index.html.twig', array(
            'logs' => $logs,
        ));
    }
}
