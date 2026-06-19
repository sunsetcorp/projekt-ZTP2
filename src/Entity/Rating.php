<?php

/**
 * Rating entity.
 */

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Rating.
 *
 * @ORM\Entity(repositoryClass=RatingRepository::class)
 *
 * @ORM\Table(name="ratings")
 */
#[ORM\Entity(repositoryClass: RatingRepository::class)]
class Rating
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Value.
     */
    #[ORM\Column(nullable: true)]
    private ?int $value = null;

    /**
     * User.
     */
    #[ORM\ManyToOne(inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Album.
     */
    #[ORM\ManyToOne(inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for Value.
     *
     * @return int|null Value
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * Setter for Value.
     *
     * @param ?int $value Value
     *
     * @return static returns the instance of the current class
     */
    public function setValue(?int $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Getter for User.
     *
     * @return User|null the user who rated the album
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Setter for User.
     *
     * @param User|null $user the user who rated the album
     *
     * @return static returns the instance of the current class
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Getter for album.
     *
     * @return Album|null The album entity
     */
    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    /**
     * Setter for album.
     *
     * @param Album|null $album The album entity to set
     *
     * @return static returns the instance of the current class
     */
    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }
}
