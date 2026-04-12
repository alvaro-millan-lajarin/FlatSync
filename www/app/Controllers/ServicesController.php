<?php

namespace App\Controllers;

class ServicesController extends BaseController
{
    private array $categories = [
        [
            'key'   => 'cleaning',
            'label' => 'Limpieza',
            'icon'  => 'sparkles',
            'color' => 'accent',
            'tags'  => [
                ['craft'   => 'cleaning'],
                ['shop'    => 'dry_cleaning'],
                ['shop'    => 'laundry'],
                ['amenity' => 'laundry'],
                ['shop'    => 'cleaning'],
                ['trade'   => 'cleaning'],
            ],
        ],
        [
            'key'   => 'plumber',
            'label' => 'Fontanería',
            'icon'  => 'droplets',
            'color' => 'warning',
            'tags'  => [
                ['craft' => 'plumber'],
                ['trade' => 'plumber'],
                ['shop'  => 'plumbing'],
                ['craft' => 'hvac'],
                ['trade' => 'hvac'],
            ],
        ],
        [
            'key'   => 'locksmith',
            'label' => 'Cerrajería',
            'icon'  => 'key-round',
            'color' => 'success',
            'tags'  => [
                ['craft' => 'locksmith'],
                ['trade' => 'locksmith'],
                ['shop'  => 'locksmith'],
            ],
        ],
        [
            'key'   => 'electrician',
            'label' => 'Electricidad',
            'icon'  => 'zap',
            'color' => 'danger',
            'tags'  => [
                ['craft' => 'electrician'],
                ['trade' => 'electrician'],
                ['shop'  => 'electrical'],
                ['craft' => 'electronics_repair'],
            ],
        ],
        [
            'key'   => 'painter',
            'label' => 'Pintura',
            'icon'  => 'paintbrush',
            'color' => 'accent',
            'tags'  => [
                ['craft' => 'painter'],
                ['trade' => 'painter'],
                ['shop'  => 'paint'],
            ],
        ],
        [
            'key'   => 'moving',
            'label' => 'Mudanzas',
            'icon'  => 'truck',
            'color' => 'warning',
            'tags'  => [
                ['shop'    => 'relocation'],
                ['office'  => 'moving_company'],
                ['amenity' => 'storage_rental'],
                ['shop'    => 'storage_rental'],
                ['craft'   => 'transport'],
            ],
        ],
    ];

    public function index()
    {
        return view('services/index', [
            'pageTitle'    => 'Servicios cercanos',
            'pageSubtitle' => 'Profesionales de confianza cerca de tu hogar',
            'activeNav'    => 'services',
            'categories'   => $this->categories,
        ]);
    }

    /**
     * Endpoint AJAX: GET /services/nearby?lat=…&lng=…&type=…
     * Consulta Overpass API (OpenStreetMap) — sin API key, totalmente gratuito.
     */
    public function nearby()
    {
        $lat  = (float) $this->request->getGet('lat');
        $lng  = (float) $this->request->getGet('lng');
        $type = $this->request->getGet('type');

        $allowedKeys = array_column($this->categories, 'key');
        if (!$lat || !$lng || !in_array($type, $allowedKeys, true)) {
            return $this->response->setJSON(['error' => 'Parámetros inválidos'])->setStatusCode(400);
        }

        $cat = $this->categories[array_search($type, $allowedKeys)];

        $query  = $this->buildOverpassQuery($cat['tags'], $lat, $lng, 50000);
        $raw    = $this->overpassFetch($query);

        if ($raw === false) {
            return $this->response->setJSON(['error' => 'No se pudo conectar con Overpass API'])->setStatusCode(502);
        }

        $data    = json_decode($raw, true);
        $results = $this->parseElements($data['elements'] ?? [], $lat, $lng);

        // Ordenar por distancia
        usort($results, fn($a, $b) => $a['distance_m'] <=> $b['distance_m']);

        return $this->response->setJSON(['results' => array_slice($results, 0, 10)]);
    }

    // ── Construcción de la query Overpass QL ─────────────────────────────

    private function buildOverpassQuery(array $tags, float $lat, float $lng, int $radius): string
    {
        $lines = '';
        foreach ($tags as $tag) {
            foreach ($tag as $k => $v) {
                $filter = '["' . $k . '"="' . $v . '"](around:' . $radius . ',' . $lat . ',' . $lng . ');';
                $lines .= "node$filter\n  way$filter\n  ";
            }
        }
        return '[out:json][timeout:15];(' . $lines . ');out center 20;';
    }

    private function overpassFetch(string $query): string|false
    {
        $url = 'https://overpass-api.de/api/interpreter';
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => 'data=' . urlencode($query),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_USERAGENT      => 'FlatSync/1.0',
        ]);
        $result = curl_exec($ch);
        $error  = curl_error($ch);
        curl_close($ch);
        return $error ? false : $result;
    }

    // ── Parseo de resultados ──────────────────────────────────────────────

    private function parseElements(array $elements, float $userLat, float $userLng): array
    {
        $seen    = [];
        $results = [];

        foreach ($elements as $el) {
            $tags = $el['tags'] ?? [];
            $name = $tags['name'] ?? null;
            if (!$name) continue;

            // Coordenadas (node directo o centroide de way)
            $elLat = $el['lat'] ?? $el['center']['lat'] ?? null;
            $elLng = $el['lon'] ?? $el['center']['lon'] ?? null;
            if (!$elLat || !$elLng) continue;

            // Evitar duplicados por nombre+posición
            $dedup = $name . round($elLat, 3) . round($elLng, 3);
            if (isset($seen[$dedup])) continue;
            $seen[$dedup] = true;

            $distM = $this->haversine($userLat, $userLng, $elLat, $elLng);

            $results[] = [
                'name'       => $name,
                'address'    => $this->buildAddress($tags),
                'phone'      => $tags['phone'] ?? $tags['contact:phone'] ?? null,
                'website'    => $tags['website'] ?? $tags['contact:website'] ?? null,
                'hours'      => $tags['opening_hours'] ?? null,
                'distance_m' => (int) $distM,
                'distance'   => $this->formatDistance($distM),
                'osm_type'   => $el['type'],
                'osm_id'     => $el['id'],
                'lat'        => $elLat,
                'lng'        => $elLng,
            ];
        }

        return $results;
    }

    private function buildAddress(array $tags): string
    {
        $parts = array_filter([
            $tags['addr:street']  ?? null,
            $tags['addr:housenumber'] ?? null,
            $tags['addr:city']    ?? null,
        ]);
        return implode(', ', $parts);
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function formatDistance(float $meters): string
    {
        return $meters < 1000
            ? round($meters) . ' m'
            : number_format($meters / 1000, 1) . ' km';
    }
}
