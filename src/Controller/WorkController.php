<?php


namespace App\Controller;


use App\Repository\WorkRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;

/**
 * Class WorkController
 * @package App\Controller
 *
 * @Route("/api/v1",name="api_")
 */
class WorkController extends AbstractFOSRestController
{
    /**
     * @param WorkRepository $workRepository
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/works",name="works")
     */
    public function getWorks(WorkRepository $workRepository)
    {
        $works = $workRepository->findAll();
        $view = $this->view($works, 200);
        $view->getContext()->setGroups(['work_details']);

        $response = $this->handleView($view);

        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}