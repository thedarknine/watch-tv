<?php

/**
 * This file is part of Watch TV project.
 * (c) TheDarkNine <hello@carolinenoyer.fr>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Channel;
use App\Repository\CategoryRepository;
use App\Repository\ChannelRepository;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class InitProgramsController extends AbstractController
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    #[Route('/init/programs', name: 'app_init_programs', methods: ['GET'])]
    public function index(ChannelRepository $channelRepository, CategoryRepository $categoryRepository): Response
    {
        // Retrieve channels list
        $listChannels = $this->client->request('GET', 'https://xmltv.digital3d.com/api/GetChannelIds');
        foreach ($listChannels->toArray() as $channel) {
            // Check if exists
            $tmpChannel = $channelRepository->findOneBy(['externalId' => $channel['channelId']]);
            if ($tmpChannel) {
                $tmpChannel->setName($channel['name']);
                $tmpChannel->setUrl($channel['channelIpTvId']);
                $tmpChannel->setLogo($channel['icon']);
                $tmpChannel->setUpdatedAt(new \DateTimeImmutable());
            } else {
                $tmpChannel = new Channel();
                $tmpChannel->setExternalId($channel['channelId']);
                $tmpChannel->setName($channel['name']);
                $tmpChannel->setUrl($channel['channelIpTvId']);
                $tmpChannel->setLogo($channel['icon']);
                $tmpChannel->setCreatedAt(new \DateTimeImmutable());
            }
            $channelRepository->save($tmpChannel);
        }

        // Retrieve categories list
        $listCategories = $this->client->request('GET', 'https://xmltv.digital3d.com/api/GetCategories');
        foreach ($listCategories->toArray() as $category) {
            // Check if exists
            $tmpCategory = $categoryRepository->findOneBy(['name' => $category]);
            if ($tmpCategory) {
                $tmpCategory->setName($category);
                $tmpCategory->setSlug((new Slugify())->slugify($category));
                $tmpCategory->setUpdatedAt(new \DateTimeImmutable());
            } else {
                $tmpCategory = new Category();
                $tmpCategory->setName($category);
                $tmpCategory->setSlug((new Slugify())->slugify($category));
                $tmpCategory->setCreatedAt(new \DateTimeImmutable());
            }
            $categoryRepository->save($tmpCategory);
        }

        // Add favorites
        $listFavorites = [
            'bein-sports-1-183', 'bein-sports-2-184', 'bein-sports-3-265', 'canalplus-2', 'canalplus-cinema-35',
            'canalplus-docs-9501', 'canalplus-live-1-10503', 'canalplus-live-2-10507', 'canalplus-live-3-10505',
            'canalplus-series-227', 'canalplus-sport-37', 'cherie-25-206', 'eurosport-1-5', 'eurosport-2-63',
            'france-2-6', 'france-3-7', 'lequipe-204', 'm6-12', 'planeteplus-crime-investigation-151',
            'rmc-decouverte-205', 'rmc-story-203', 'tf1-19', 'tf1-series-films-201', 'tfx-14', 'tmc-21', 'w9-24',
            'warner-tv-403', 'cherie-25-206', 'nrj-12-13',
            'canalplus-box-office-11522',
            // "ocs" "paramount+" "action" "tcm" "serie-club" "tv-breizh" "rtl9" "paris-premiere" "teva"
        ];
        foreach ($listFavorites as $favorite) {
            $tmpChannel = $channelRepository->findOneBy(['externalId' => $favorite]);
            if ($tmpChannel) {
                $tmpChannel->setFavorite(true);
                $channelRepository->save($tmpChannel);
            }
        }

        return $this->render('init_programs/index.html.twig', [
            'controller_name' => 'InitProgramsController',
            'data' => [
                'nbChannels' => count($listChannels->toArray()),
                'nbCategories' => count($listCategories->toArray()),
                'channels' => $listChannels->toArray(),
            ],
        ]);
    }
}
