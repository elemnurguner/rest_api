<?php
use PHPUnit\Framework\TestCase;

class BooksApiTest extends TestCase
{
     private $apiUrl = 'http://localhost/rest_api/books';

     public function testGetBooks()
     {
          $response = file_get_contents($this->apiUrl);
          $data = json_decode($response, true);

          $this->assertArrayHasKey('type', $data);
          $this->assertEquals('success', $data['type']);
     }

     public function testPostBook()
     {
          $data = json_encode([
               'title' => 'New Book',
               'author' => 'Author Name',
               'isbn' => '1234567890',
               'price' => 19.99
          ]);

          $context = stream_context_create([
               'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $data
               ]
          ]);

          $response = file_get_contents($this->apiUrl, false, $context);
          $response_data = json_decode($response, true);

          $this->assertEquals('success', $response_data['type']);
     }

     public function testPutBook()
     {
          // Önce bir kitap ekleyin
          $data = json_encode([
               'title' => 'Updated Book',
               'author' => 'Updated Author',
               'isbn' => '0987654321',
               'price' => 29.99
          ]);

          $context = stream_context_create([
               'http' => [
                    'method' => 'PUT',
                    'header' => 'Content-Type: application/json',
                    'content' => $data
               ]
          ]);

          $response = file_get_contents($this->apiUrl . '/1', false, $context); // ID 1 olan kitabı güncelle
          $response_data = json_decode($response, true);

          $this->assertEquals('success', $response_data['type']);
     }

     public function testDeleteBook()
     {
          $context = stream_context_create([
               'http' => [
                    'method' => 'DELETE',
                    'header' => 'Content-Type: application/json',
               ]
          ]);

          $response = file_get_contents($this->apiUrl . '/1', false, $context); // ID 1 olan kitabı sil
          $response_data = json_decode($response, true);

          $this->assertEquals('success', $response_data['type']);
     }
}
