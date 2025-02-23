<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Action', 'Aventure', 'RPG', 'Simulation', 'Stratégie',
            'Sports', 'FPS', 'MMORPG', 'Course', 'Puzzle', 'Open World', 'Platformer',
        ];

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);

            $manager->persist($category);
            $this->addReference('category_' . str_replace(' ', '_', strtolower($categoryName)), $category);
        }

        $manager->flush();
    }
}
