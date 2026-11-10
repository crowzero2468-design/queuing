<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Queue System Routes
$routes->get('/', 'Queue::kiosk');
// $routes->get('/', 'Queue::index');
$routes->get('queue', 'Queue::index');
$routes->get('queue/kiosk', 'Queue::kiosk');
$routes->get('queue/room/(:num)', 'Queue::room/$1');
$routes->get('queue/call_room/(:num)', 'Queue::callRoom/$1');
$routes->get('queue/skip/(:num)', 'Queue::skip/$1');
// Keep old hold route for compatibility but map it to skip behavior
$routes->get('queue/hold/(:num)', 'Queue::skip/$1');
$routes->get('queue/resume/(:num)', 'Queue::resume/$1');
$routes->get('queue/resume/(:num)/(:num)', 'Queue::resume/$1/$2');
$routes->get('queue/repeat/(:num)', 'Queue::repeat/$1');
$routes->get('queue/reset', 'Queue::reset');
$routes->get('queue/api/status', 'Queue::getRoomStatus');
$routes->get('queue/api/count', 'Queue::getWaitingCount');
$routes->post('queue/generate', 'Queue::generate');
$routes->get('queue/serve_next/(:num)', 'Queue::serveNext/$1');
// legacy hardcoded moves (may exist from previous versions)
$routes->get('queue/move_to_room5/(:num)', 'Queue::moveToRoom5/$1');

// generic move route: from current room to a given target room
$routes->get('queue/move_to_room/(:num)/(:num)', 'Queue::moveToRoom/$1/$2');
$routes->get('queue/calloff', 'Queue::callOff');
$routes->get('queue/api/display', 'Queue::apiDisplay');
$routes->get('queue/api/display/(:num)', 'Queue::apiRoomDisplay/$1');
$routes->get('queue/view/(:num)', 'Queue::roomView/$1');
$routes->get('queue/api/room/(:num)', 'Queue::apiRoom/$1');
$routes->get('queue/print_view', 'Queue::printView');
$routes->get('queue/print_view/(:any)/(:num)', 'Queue::printView/$1/$2');