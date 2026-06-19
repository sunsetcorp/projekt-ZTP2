<?php

/**
 * Tag type test.
 */

namespace App\Tests\Form\Type;

use App\Entity\Tag;
use App\Form\Type\TagType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class TagTypeTest.
 */
class TagTypeTest extends TypeTestCase
{
    /**
     * Test submission of valid data.
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Rock',
        ];

        $model = new Tag();

        $form = $this->factory->create(TagType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $this->assertSame('Rock', $model->getTitle());
    }

    /**
     * Test if form has a title field.
     */
    public function testFormHasTitleField(): void
    {
        $form = $this->factory->create(TagType::class);

        $this->assertTrue($form->has('title'));
    }
}
