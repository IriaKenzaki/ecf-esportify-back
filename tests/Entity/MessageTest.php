<?php

namespace App\Tests\Entity;

use App\Entity\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testCreateMessage(): void
    {
        $message = new Message();
        $message->setUser('JohnDoe');
        $message->setTitle('Test Title');
        $message->setText('This is a test message.');

        $this->assertEquals('JohnDoe', $message->getUser());
        $this->assertEquals('Test Title', $message->getTitle());
        $this->assertEquals('This is a test message.', $message->getText());
    }
}