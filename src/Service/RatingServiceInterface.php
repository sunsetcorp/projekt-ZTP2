<?php

/**
 * Rating service interface.
 */

namespace App\Service;

use App\Entity\Album;
use App\Entity\User;

/**
 * Class RatingServiceInterface.
 **/
interface RatingServiceInterface
{
    /**
     * The album rating function.
     *
     * @param Album $album The album to rate
     * @param User  $user  The user who rates
     * @param int   $value Value of the rating
     */
    public function rate(Album $album, User $user, int $value): void;
}
