<?php

namespace App\DataFixtures;

use App\Entity\Editor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EditorFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $editors = [
            ['name' => 'Nintendo', 'country' => 'Japan', 'key' => 'editor_nintendo'],
            ['name' => 'Sony Interactive Entertainment', 'country' => 'Japan', 'key' => 'editor_sony'],
            ['name' => 'Microsoft Studios', 'country' => 'USA', 'key' => 'editor_microsoft_studios'],
            ['name' => 'Ubisoft', 'country' => 'France', 'key' => 'editor_ubisoft'],
            ['name' => 'Electronic Arts', 'country' => 'USA', 'key' => 'editor_electronic_arts'],
            ['name' => 'Bethesda', 'country' => 'USA', 'key' => 'editor_bethesda'],
            ['name' => 'Rockstar Games', 'country' => 'USA', 'key' => 'editor_rockstar_games'],
            ['name' => 'Square Enix', 'country' => 'Japan', 'key' => 'editor_square_enix'],
            ['name' => 'Team Cherry', 'country' => 'Australia', 'key' => 'editor_team_cherry'],
            ['name' => 'CD Projekt', 'country' => 'Poland', 'key' => 'editor_cd_projekt'],
            ['name' => 'Sony', 'country' => 'Japan', 'key' => 'editor_cd_projekt'],
        ];

        foreach ($editors as $editorData) {
            $editor = new Editor();
            $editor->setName($editorData['name']);
            $editor->setCountry($editorData['country']);

            $manager->persist($editor);
            $this->addReference('editor_' . strtolower(str_replace(' ', '_', $editorData['name'])), $editor);
        }

        $manager->flush();
    }
}
