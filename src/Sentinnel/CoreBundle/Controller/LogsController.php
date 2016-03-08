<?php

namespace Sentinnel\CoreBundle\Controller;

use Sentinnel\CoreBundle\Form\Type\LogsFilterType;
use Sentinnel\CoreBundle\Manager\LogsManager;
use Sentinnel\CoreBundle\Manager\ProfilerManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
    public function indexAction(Request $request)
    {
        $systemDir = '/home/dundivet/htdocs/www/buses.taller';
        $dir = $systemDir . '/app/logs/dev.log';
        $logsManager = new LogsManager();

        $logs = $logsManager->findAll($dir);
//        $grep = preg_grep("#^\[2015-06-07 11:30:17\]#", $logs);

        $form = $this->createForm(new LogsFilterType(), null, array(
            'method' => 'POST',
            'action' => $this->generateUrl('logs_list'),
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getNormData();
            $grep = preg_grep(sprintf('#%s#', $data['type']), $logs);
        } else {
            $grep = array();
        }

        return $this->render('@SentinnelCore/Logs/index.html.twig', array(
            'logs' => $grep,
            'form' => $form->createView(),
        ));
    }
}
