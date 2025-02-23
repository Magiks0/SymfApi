<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Editor;
use App\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VideoGameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $videoGames = [
            [
                'title' => 'The Legend of Zelda: Breath of the Wild',
                'releaseDate' => '2017-03-03',
                'description' => 'An open-world action-adventure game set in the vast kingdom of Hyrule.',
                'editor' => 'editor_nintendo',
                'categories' => ['category_action', 'category_aventure'],
            ],
            [
                'title' => 'Halo Infinite',
                'releaseDate' => '2021-12-08',
                'description' => 'A sci-fi first-person shooter and the latest installment in the Halo franchise.',
                'editor' => 'editor_microsoft_studios',
                'categories' => ['category_fps', 'category_action'],
            ],
            [
                'title' => 'Assassin’s Creed Valhalla',
                'releaseDate' => '2020-11-10',
                'description' => 'An action RPG set during the Viking Age, exploring the life of Eivor.',
                'editor' => 'editor_ubisoft',
                'categories' => ['category_rpg', 'category_action', 'category_aventure'],
            ],
            [
                'title' => 'Starfield',
                'releaseDate' => '2025-02-16',
                'description' => 'A sci-fi RPG set in space, offering vast exploration and deep storytelling.',
                'editor' => 'editor_bethesda',
                'categories' => ['category_rpg', 'category_action', 'category_open_world'],
                'coverImage' => 'starfield.jpeg',
            ],
            [
                'title' => 'The Elder Scrolls VI',
                'releaseDate' => '2026-12-01',
                'description' => 'The long-awaited next chapter in the legendary Elder Scrolls series.',
                'editor' => 'editor_bethesda',
                'categories' => ['category_rpg', 'category_open_world'],
            ],
            [
                'title' => 'Grand Theft Auto VI',
                'releaseDate' => '2025-10-15',
                'description' => 'The next entry in Rockstars open-world crime saga.',
                'editor' => 'editor_rockstar_games',
                'categories' => ['category_action', 'category_open_world'],
            ],
            [
                'title' => 'Final Fantasy XVI',
                'releaseDate' => '2025-02-18',
                'description' => 'An epic RPG adventure in the Final Fantasy universe.',
                'editor' => 'editor_square_enix',
                'categories' => ['category_rpg', 'category_action'],
            ],
            [
                'title' => 'Marvel’s Wolverine',
                'releaseDate' => '2025-02-17',
                'description' => 'An action-adventure game featuring the legendary X-Men hero.',
                'editor' => 'editor_sony',
                'categories' => ['category_action', 'category_aventure'],
            ],
            [
                'title' => 'Hollow Knight: Silksong',
                'releaseDate' => '2024-02-14',
                'description' => 'A metroidvania adventure set in a beautifully animated world.',
                'editor' => 'editor_team_cherry',
                'categories' => ['category_platformer', 'category_aventure'],
            ],
            [
                'title' => 'The Witcher 4',
                'releaseDate' => '2026-11-20',
                'description' => 'A new saga in the Witcher universe, focusing on a fresh protagonist.',
                'editor' => 'editor_cd_projekt',
                'categories' => ['category_rpg', 'category_action', 'category_aventure'],
            ],
        ];

        foreach ($videoGames as $gameData) {
            $videoGame = (new VideoGame())
                ->setTitle($gameData['title'])
                ->setReleaseDate(new DateTime($gameData['releaseDate']))
                ->setDescription($gameData['description'])
                ->setEditor($this->getReference($gameData['editor'], Editor::class));

            if (isset($gameData['coverImage'])) {
                $imagePath = 'images/' . $gameData['coverImage'];
                $videoGame->setCoverImage($imagePath);
            }

            foreach ($gameData['categories'] as $categoryReference) {
                $videoGame->addCategory($this->getReference($categoryReference, Category::class));
            }

            $manager->persist($videoGame);
        }
        $manager->flush();
    }
}
