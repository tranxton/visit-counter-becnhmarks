<?php

declare(strict_types=1);

namespace App\Modules\Visits\Infrastructure\Controllers;

use App\Modules\Visits\Application\UseCases\GetVisits\Exceptions\GetVisitsException;
use App\Modules\Visits\Application\UseCases\GetVisits\GetVisitsUseCase;
use App\Modules\Visits\Application\UseCases\RegisterVisit\Exceptions\RegisterVisitException;
use App\Modules\Visits\Application\UseCases\RegisterVisit\RegisterVisitUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisitController extends AbstractController
{
    public function __construct(
        private readonly RegisterVisitUseCase $registerVisitUseCase,
        private readonly GetVisitsUseCase $getVisitsUseCase
    ) {
    }

    #[Route('/visits/{country}', methods: ['POST'])]
    public function registerVisit(string $country): Response
    {
        try {
            $counter = $this->registerVisitUseCase->run(country: $country);
        } catch (RegisterVisitException $e) {
            return new JsonResponse(data: ['error' => $e->getMessage()], status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(data: ['visits' => [$country => $counter]]);
    }

    #[Route('/visits', methods: ['GET'])]
    public function statistics(): Response
    {
        try {
            $visits = $this->getVisitsUseCase->run();
        } catch (GetVisitsException $e) {
            return new JsonResponse(data: ['error' => $e->getMessage()], status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(data: ['visits' => $visits]);
    }
}
