<?php

/**
 * This file is part of Watch TV project.
 * (c) TheDarkNine <hello@carolinenoyer.fr>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Program;
use App\Repository\CategoryRepository;
use App\Repository\ChannelRepository;
use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SyncProgramsController extends AbstractController
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    #[Route('/sync/programs/{date}', name: 'app_sync_programs', methods: ['GET'])]
    public function index(string $date, ChannelRepository $channelRepository, CategoryRepository $categoryRepository, ProgramRepository $programRepository): Response
    {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
            echo 'Invalid date format';

            return $this->render('sync_programs/index.html.twig', [
                'controller_name' => 'SyncController',
                'data' => [
                    'channels' => [],
                ],
            ]);
        }

        $day = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        echo $day->format('Y/m/d').'<br>';

        // Retrieve wanted channels list
        $listChannels = $channelRepository->findByFavorite();
        foreach ($listChannels as $channel) {
            $listPrograms = $this->client->request('GET', 'https://xmltv.digital3d.com/api/GetTvPrograms?channelId='.$channel->getExternalId().'&date='.$day->format('Y/m/d').'&limit=50');
            echo $channel->getExternalId().' => '.count($listPrograms->toArray()).'<br>';
            foreach ($listPrograms->toArray() as $program) {
                $tmpProgram = $programRepository->findOneBy([
                    'title' => $program['title'],
                    'channel' => $channelRepository->findOneBy(['externalId' => $program['channelId']]),
                    'beginAt' => new \DateTimeImmutable($program['startDateTime']),
                ]);
                if ($tmpProgram instanceof Program) {
                    $tmpProgram = $programRepository->findOneBy([
                        'title' => $program['title'],
                        'channel' => $channelRepository->findOneBy(['externalId' => $program['channelId']]),
                        'beginAt' => new \DateTimeImmutable($program['startDateTime']),
                    ]);
                    $tmpProgram->setCategory($categoryRepository->findOneBy(['name' => $program['category']]));
                    $tmpProgram->setDescription($program['description']);
                    $tmpProgram->setBeginAt(new \DateTimeImmutable($program['startDateTime']));
                    $tmpProgram->setEndAt(new \DateTimeImmutable($program['stopDateTime']));
                    $tmpProgram->setPicture($program['picture']);
                    $tmpProgram->setYear($program['year']);
                    $tmpProgram->setUpdatedAt(new \DateTimeImmutable());
                    $programRepository->save($tmpProgram);
                } else {
                    $tmpProgram = new Program();
                    $tmpProgram->setChannel($channelRepository->findOneBy(['externalId' => $program['channelId']]));
                    $tmpProgram->setCategory($categoryRepository->findOneBy(['name' => $program['category']]));
                    $tmpProgram->setTitle($program['title']);
                    $tmpProgram->setDescription($program['description']);
                    $tmpProgram->setBeginAt(new \DateTimeImmutable($program['startDateTime']));
                    $tmpProgram->setEndAt(new \DateTimeImmutable($program['stopDateTime']));
                    $tmpProgram->setPicture($program['picture']);
                    $tmpProgram->setYear($program['year']);
                    $tmpProgram->setCreatedAt(new \DateTimeImmutable());
                    $programRepository->save($tmpProgram);
                }
            }
            unset($channel, $listPrograms, $tmpProgram);
            gc_collect_cycles();
        }

        return $this->render('sync_programs/index.html.twig', [
            'controller_name' => 'SyncController',
            'data' => [
                'channels' => $listChannels,
            ],
        ]);
    }
}
