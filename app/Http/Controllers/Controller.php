<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="HMCA API",
 *     version="1.0.0",
 * )
 * @OA\OpenApi(
 *   security={{"bearerAuth":{}}}
 *      )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller {}
