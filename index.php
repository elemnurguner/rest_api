<?php
require 'config.php';


function validateApiKey($apiKey)
{
     $validApiKey = '8f9c9cbdb0c57a4bc4d630a5297ac180830c348e899c9b5a22091460c4f19527';
     return $apiKey === $validApiKey;
}


$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));


$headers = getallheaders();
if (!isset($headers['X-API-Key']) || !validateApiKey($headers['X-API-Key'])) {
     header('HTTP/1.1 401 Unauthorized');
     echo json_encode(['error' => 'Invalid API key']);
     exit();
}


switch ($method) {
     case 'GET':
          if ($request[0] === 'books') {
               if (isset($request[1])) {
                    // Get book by ID
                    $kitap_id = filter_var($request[1], FILTER_VALIDATE_INT);
                    if ($kitap_id === false) {
                         header('HTTP/1.1 400 Bad Request');
                         echo json_encode(['type' => 'error', 'message' => 'Invalid book ID']);
                         exit();
                    }

                    try {
                         $kitaplar = DB::GET("SELECT * FROM books WHERE id = ?", [$kitap_id]);
                         if ($kitaplar) {
                              header('HTTP/1.1 200 OK');
                              echo json_encode(['type' => 'success', 'data' => $kitaplar]);
                         } else {
                              header('HTTP/1.1 404 Not Found');
                              echo json_encode(['type' => 'error', 'message' => 'Kitap bulunamadı']);
                         }
                    } catch (Exception $e) {
                         header('HTTP/1.1 500 Internal Server Error');
                         echo json_encode(['type' => 'error', 'message' => 'Veritabanı hatası', 'error' => $e->getMessage()]);
                    }
               } else {
                    // List all books
                    try {
                         $kitaplar = DB::GET("SELECT * FROM books");
                         header('HTTP/1.1 200 OK');
                         echo json_encode([
                              'type' => 'success',
                              'data' => $kitaplar,
                              'count' => count($kitaplar)
                         ]);
                    } catch (Exception $e) {
                         header('HTTP/1.1 500 Internal Server Error');
                         echo json_encode(['type' => 'error', 'message' => 'Veritabanı hatası', 'error' => $e->getMessage()]);
                    }
               }
          }
          break;

     case 'POST':
          if ($request[0] === 'books') {
               $data = json_decode(file_get_contents('php://input'), true);
               $title = filter_var($data['title'], FILTER_SANITIZE_STRING);
               $author = filter_var($data['author'], FILTER_SANITIZE_STRING);
               $isbn = filter_var($data['isbn'], FILTER_SANITIZE_STRING);
               $price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);

               if ($title === false || $author === false || $isbn === false || $price === false) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['type' => 'error', 'message' => 'Invalid input data']);
                    exit();
               }

               try {
                    $id = DB::insert("INSERT INTO books (title, author, isbn, price) VALUES (?, ?, ?, ?)", [
                         $title,
                         $author,
                         $isbn,
                         $price
                    ]);
                    header('HTTP/1.1 201 Created');
                    echo json_encode(['type' => 'success', 'message' => 'Kitap başarıyla eklendi']);
               } catch (Exception $e) {
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['type' => 'error', 'message' => 'Veritabanı hatası', 'error' => $e->getMessage()]);
               }
          }
          break;

     case 'PUT':
          if ($request[0] === 'books' && isset($request[1])) {
               $data = json_decode(file_get_contents('php://input'), true);
               $title = filter_var($data['title'], FILTER_SANITIZE_STRING);
               $author = filter_var($data['author'], FILTER_SANITIZE_STRING);
               $isbn = filter_var($data['isbn'], FILTER_SANITIZE_STRING);
               $price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
               $kitap_id = filter_var($request[1], FILTER_VALIDATE_INT);

               if ($title === false || $author === false || $isbn === false || $price === false || $kitap_id === false) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['type' => 'error', 'message' => 'Invalid input data']);
                    exit();
               }

               try {
                    $result = DB::exec(
                         "UPDATE books SET title = ?, author = ?, isbn = ?, price = ? WHERE id = ?",
                         [$title, $author, $isbn, $price, $kitap_id]
                    );

                    if ($result) {
                         header('HTTP/1.1 200 OK');
                         echo json_encode(['type' => 'success', 'message' => 'Kitap başarıyla güncellendi']);
                    } else {
                         header('HTTP/1.1 500 Internal Server Error');
                         echo json_encode(['type' => 'error', 'message' => 'Kitap güncellenirken bir hata oluştu']);
                    }
               } catch (Exception $e) {
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['type' => 'error', 'message' => 'Veritabanı hatası', 'error' => $e->getMessage()]);
               }
          }
          break;

     case 'DELETE':
          if ($request[0] === 'books' && isset($request[1])) {
               $kitap_id = filter_var($request[1], FILTER_VALIDATE_INT);
               if ($kitap_id === false) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['type' => 'error', 'message' => 'Invalid book ID']);
                    exit();
               }

               try {
                    $result = DB::exec(
                         "DELETE FROM books WHERE id = ?",
                         [$kitap_id]
                    );

                    if ($result) {
                         header('HTTP/1.1 200 OK');
                         echo json_encode(['type' => 'success', 'message' => 'Kitap başarıyla silindi']);
                    } else {
                         header('HTTP/1.1 500 Internal Server Error');
                         echo json_encode(['type' => 'error', 'message' => 'Kitap silinirken bir hata oluştu']);
                    }
               } catch (Exception $e) {
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['type' => 'error', 'message' => 'Veritabanı hatası', 'error' => $e->getMessage()]);
               }
          }
          break;

     default:
          header('HTTP/1.1 405 Method Not Allowed');
          echo json_encode(['type' => 'error', 'message' => 'Method not allowed']);
          break;
}
?>