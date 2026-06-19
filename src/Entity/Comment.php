<?php

/**
 * Comment entity.
 */

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Comment.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Content of the comment.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    /**
     * Creation date of the comment.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Author of the comment.
     */
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * Album to which the comment belongs.
     */
    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    /**
     * Getter for Id.
     *
     * @return int|null Returns the ID of the album, or null if not set
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for content.
     *
     * @return string|null Returns the content, or null if not set
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Setter for content.
     *
     * @param string|null $content The content to set
     *
     * @return static returns the instance of the current class
     */
    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Getter for createdAt.
     *
     * @return int|null Id
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for createdAt.
     *
     * @param \DateTimeImmutable|null $createdAt
     *
     * @return static returns the instance of the current class
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Getter for author.
     *
     * @return User|null Returns the author of the album, or null if not set
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for author.
     *
     * @param string|null $author The author of the album
     *
     * @return static returns the instance of the current class
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

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
