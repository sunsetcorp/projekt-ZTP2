<?php

/**
 * Rating service.
 */

namespace App\Service;

use App\Entity\Album;
use App\Entity\User;
use App\Entity\Rating;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RatingService.
 *
 * Service responsible for user rating.
 */
class RatingService implements RatingServiceInterface
{
    /**
     * RegistrationService constructor.
     *
     * @param RatingRepository       $ratingRepository The rating repository
     * @param EntityManagerInterface $em               The entity manager
     */
    public function __construct(private readonly RatingRepository $ratingRepository, private readonly EntityManagerInterface $em)
    {
    }

    /**
     * The album rating function.
     *
     * @param Album $album The album to rate
     * @param User  $user  The user who rates
     * @param int   $value Value of the rating
     */
    public function rate(Album $album, User $user, int $value): void
    {
        $rating = $this->ratingRepository->findOneBy([
            'album' => $album,
            'user' => $user,
        ]);

        if (!$rating) {
            $rating = new Rating();
            $rating->setAlbum($album);
            $rating->setUser($user);
        }

        $rating->setValue($value);

        $this->em->persist($rating);
        $this->em->flush();
    }
}
