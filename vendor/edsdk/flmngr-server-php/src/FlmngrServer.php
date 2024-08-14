<?php

/**
 *
 * Flmngr server package for PHP.
 *
 * This file is a part of the server side implementation of Flmngr -
 * the JavaScript/TypeScript file manager widely used for building apps and editors.
 *
 * Comes as a standalone package for custom integrations,
 * and as a part of N1ED web content builder.
 *
 * Flmngr file manager:       https://flmngr.com
 * N1ED web content builder:  https://n1ed.com
 * Developer website:         https://edsdk.com
 *
 * License: GNU General Public License Version 3 or later
 *
 **/

namespace EdSDK\FlmngrServer;

use EdSDK\FlmngrServer\fs\FileSystem;
use EdSDK\FlmngrServer\lib\CommonRequest;
use EdSDK\FlmngrServer\resp\Response;
use Exception;

use EdSDK\FlmngrServer\lib\JsonCodec;
use EdSDK\FlmngrServer\model\Message;
use EdSDK\FlmngrServer\lib\MessageException;

ini_set('display_errors', 0);

class FlmngrServer {

  static function flmngrRequest($config) {

    if (!isset($config['dirCache']) && isset($config['driverFiles'])) {
      $resp = new Response("Set cache dir when using another files driver", NULL);
      $strResp = JsonCodec::s_toJson($resp);
      try {
        http_response_code(200);
        header('Content-Type: application/json; charset=UTF-8');
        print $strResp;
      } catch (Exception $e) {
        error_log($e);
      }
      return;
    }

    try {

      if (isset($config['request'])) {
        $request = $config['request'];
      }
      else {
        $request = new CommonRequest();
      }

      // Manually set (already parsed) array could be passed
      if (method_exists($request, "parseRequest")) {
        $request->parseRequest();
      }

      $codec = 0;
      if (isset($request->post['codec'])) {
        $codec = $request->post['codec'];
      } else if (isset($request->get['codec'])) {
        $codec = $request->get['codec'];
      }
      if ($codec !== 0) {
        FlmngrServer::decodeRequest($request, $codec);
        error_log(print_r($request, true));
      }

      $fileSystem = new FileSystem($config);

      if (FlmngrServer::checkUploadLimit($request)) {
        return;
      } // file size exceed the limit from php.ini

      if (isset($request->post['embedPreviews'])) {
        $fileSystem->embedPreviews = $request->post['embedPreviews'];
      }

      $action = NULL;
      if ($request->requestMethod === 'POST') {
        if (isset($request->post['action'])) {
          $action = $request->post['action'];
        }
      }
      else {
        if ($request->requestMethod === 'GET') {
          $action = $request->get['action'];
        }
        else {
          return;
        }
      }

      $data = TRUE; // will be optionally filled by request
      switch ($action) {
        case 'dirList':
          $data = $fileSystem->reqGetDirs($request);
          break;
        case 'dirCreate':
          $fileSystem->reqCreateDir($request);
          break;
        case 'dirRename':
          $fileSystem->reqRename($request);
          break;
        case 'dirDelete':
          $fileSystem->reqDeleteDir($request);
          break;
        case 'dirCopy':
          $fileSystem->reqCopyDir($request);
          break;
        case 'dirMove':
          $fileSystem->reqMove($request);
          break;
        case 'fileList':
          $data = $fileSystem->reqGetFiles($request);
          break;
        case 'fileListPaged':
          $data = $fileSystem->reqGetFilesPaged($request);
          break;
        case 'fileListSpecified':
          $data = $fileSystem->reqGetFilesSpecified($request);
          break;
        case 'fileDelete':
          $fileSystem->reqDeleteFiles($request);
          break;
        case 'fileCopy':
          $fileSystem->reqCopyFiles($request);
          break;
        case 'fileRename':
          $fileSystem->reqRename($request);
          break;
        case 'fileMove':
          $fileSystem->reqMoveFiles($request);
          break;
        case 'fileResize':
          $data = $fileSystem->reqResizeFile($request);
          break;
        case 'fileResize2':
          $data = $fileSystem->reqResizeFile2($request);
          break;
        case 'fileOriginal':
          list($mimeType, $data) = $fileSystem->reqGetImageOriginal($request);
          header('Content-Type:' . $mimeType);
          fpassthru($data);
          die();
        case 'filePreview':
          list($mimeType, $data) = $fileSystem->reqGetImagePreview($request);
          header('Content-Type:' . $mimeType);
          fpassthru($data);
          die();
        case 'filePreviewAndResolution':
          $data = $fileSystem->reqGetImagePreviewAndResolution($request);
          break;
        case 'uploadFile':
        case 'upload':
          $data = $fileSystem->reqUpload($request);
          break;
        case 'getVersion':
          $data = $fileSystem->reqGetVersion($request);
          break;
        default:
          throw new MessageException(Message::createMessage(FALSE,Message::ACTION_NOT_FOUND));
      }
      $resp = new Response(NULL, $data);
    } catch (MessageException $e) {

      if (isset($config["messageExceptionLogger"])) {
        $config["messageExceptionLogger"]($e, $request);
      } else {

        $sourceException = $e->getSourceException();

        // Log only messages with an exception
        if ($sourceException != NULL) {
          error_log("FLMNGR exception.\n");
          error_log("REQUEST:\n");
          error_log(print_r($request, TRUE)."\n");
          error_log("\n");

          error_log("RESPONSE:\n");
          error_log(print_r($e->getFailMessage(), TRUE)."\n");

          error_log("EXCEPTION:\n");
          error_log($sourceException."\n");
        }
      }

      $resp = new Response($e->getFailMessage(), NULL);
    }

    $strResp = JsonCodec::s_toJson($resp);

    try {
      http_response_code(200);
      header('Content-Type: application/json; charset=UTF-8');
      print $strResp;
    } catch (Exception $e) {
      error_log($e);
    }
  }

  private static function decodeRequest($request, $codec) {
    if ($codec == 1) {
      // Base 64 values
      foreach($request->post as $key => $value) {
        if ($key !== 'codec') {
          if (is_array($value)) {
            for ($i=0; $i<count($value); $i++) {
              $request->post[$key][$i] = base64_decode('' . $request->post[$key][$i]);
            }
          } else {
            $request->post[$key] = base64_decode('' . $value);
          }
        }
      }
      foreach($request->get as $key => $value) {
        if ($key !== 'codec') {
          if (is_array($value)) {
            for ($i=0; $i<count($value); $i++) {
              $request->get[$key][$i] = base64_decode('' . $request->get[$key][$i]);
            }
          } else {
            $request->get[$key] = base64_decode('' . $value);
          }
        }
      }
      return; // OK
    }
    throw new Exception('Unknown codec = ' . $codec . ' received from the client. You need to update the server side or check did you set Flmngr.codec correctly.');
  }

  private static function iniGetBytes($val) {
    $val = trim(ini_get($val));
    if ($val != '') {
      $last = strtolower(substr($val, strlen($val) - 1));
    }
    else {
      $last = '';
    }
    if ($last !== '') {
      $val = substr($val, 0, strlen($val) - 1);
    }

    switch ($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
        $val *= 1024;
      // fall through
      case 'm':
        $val *= 1024;
      // fall through
      case 'k':
        $val *= 1024;
      // fall through
    }

    return $val;
  }

  private static function checkUploadLimit($request) {
    $isError = FALSE;
    $maxSizeParameter = NULL;
    if (isset($_SERVER['CONTENT_LENGTH'])) {
      if (
        $_SERVER['CONTENT_LENGTH'] >
        FlmngrServer::iniGetBytes('post_max_size')
      ) {
        $isError = TRUE;
        $maxSizeParameter = 'post_max_size';
      }
    }
    if (!$isError) {
      if (isset($request->files['file'])) {
        $file = $request->files['file'];
        if ($file['tmp_name'] === '') {
          $isError = TRUE;
          $maxSizeParameter = 'upload_max_filesize';
        }
      }
    }

    if ($isError) {
      $maxSizeValueRaw = ini_get($maxSizeParameter);
      $maxSizeValueFormatted = FlmngrServer::iniGetBytes($maxSizeParameter);

      $resp = new Response(
        Message::createMessage(
          FALSE,
          Message::FILE_SIZE_EXCEEDS_SYSTEM_LIMIT,
          '' . $_SERVER['CONTENT_LENGTH'],
          '' . $maxSizeValueFormatted,
          $maxSizeParameter . " = " . $maxSizeValueRaw
        ),
        NULL
      );

      $strResp = JsonCodec::s_toJson($resp);

      try {
        http_response_code(200);
        header('Content-Type: application/json; charset=UTF-8');
        print $strResp;
      } catch (Exception $e) {
        error_log($e);
      }

      return TRUE;
    }
    else {
      return FALSE;
    }

  }

}
