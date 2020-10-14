<?php

namespace mangaslib\utilities;


use sunframework\user\UserSession;

class SlimAuthorization {
    public static function IsAdmin() {
        return function ($request, $response, $next) {
            $session = new UserSession();
            if ($session->getUserRoles() >= UserSession::ROLE_ADMIN) {
                $response = $next($request, $response);
                return $response;
            }

            return $response->withJson(['result' => 'unauthorized'], 401);
        };
    }
}