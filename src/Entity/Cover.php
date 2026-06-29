<?php

/**
 * Cover entity.
 */

namespace App\Entity;

use App\Repository\CoverRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Cover.
 *
 * @ORM\Entity(repositoryClass=CoverRepository::class)
 *
 * @ORM\Table(name="cover")
 */
#[ORM\Entity(repositoryClass: CoverRepository::class)]
class Cover
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    /**
     * Album.
     */
    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Album $album = null;

    /**
     * Filename.
     */
    #[ORM\Column(type: 'string', length: 191)]
    #[Assert\Type('string')]
    private ?string $fileName = null;

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
     * Getter for album.
     *
     * @return Album|null Album
     */
    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    /**
     * Setter for album.
     *
     * @param Album $album Album
     *
     * @return static returns the instance of the current class
     */
    public function setAlbum(Album $album): static
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Getter for filename.
     *
     * @return string|null Filename
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * Setter for filename.
     *
     * @param string $fileName Filename
     *
     * @return static returns the instance of the current class
     */
    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }
}
