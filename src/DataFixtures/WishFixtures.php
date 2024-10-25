<?php

namespace App\DataFixtures;

use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WishFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 30; $i++) {

            $wish = new Wish();

            $wish->setTitle($faker->word());
            $wish->setDescription($faker->realText());
            $wish->setAuthor($faker->name());
            $wish->setPublished(true);
            $dateCreated = $faker->dateTimeBetween('-3 months', 'now');
            $wish-> setDateCreated(new \DateTimeImmutable());
            $manager->persist($wish);

        }

        $manager->flush();
    }
}
