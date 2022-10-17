<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use PHPHtmlParser\Dom;

class Tracking extends BaseController {

    private $dom;

    public function __construct() {
        $this->dom = new Dom();
    }

    public function index() {
        $kurir = $this->request->getGet("kurir");
        $resi  = $this->request->getGet("resi");

        if (empty($kurir) || empty($resi)) {
            $data = [
                "status"  => false,
                "code"    => ResponseInterface::HTTP_NOT_FOUND,
                "message" => "Parameter tidak valid.",
            ];

            return $this->response->setJSON($data)->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        switch ($kurir) {
        case 'jne':
            return $this->jne($resi);
            break;

        default:
            $data = [
                "status"  => false,
                "code"    => ResponseInterface::HTTP_NOT_FOUND,
                "message" => "Kurir belum tersedia.",
            ];

            return $this->response->setJSON($data)->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
            break;
        }
    }

    private function jne($resi) {
        $client  = Services::curlrequest();
        $options = [
            "user_agent" => "Mozilla/5.0 (X11; Linux x86_64; rv:101.0) Gecko/20100101 Firefox/101.0",
            "headers"    => [
                "referer" => "https://www.jne.co.id",
            ],
        ];
        $response = $client->request("GET", "https://cekresi.jne.co.id/{$resi}", $options);
        $html     = $this->dom->loadStr($response->getBody());

        // jika data tidak ditemukan return error
        if (!empty($html->find(".page-content", 0))) {
            $data = [
                "status"  => false,
                "code"    => ResponseInterface::HTTP_NOT_FOUND,
                "message" => "Data tidak ditemukan.",
            ];

            return $this->response->setJSON($data)->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // Parsing html
        $no_resi = trim(str_replace("&nbsp;", "", strip_tags($html->find(".x_title h3", 0)->innerHtml)));
        $status  = [];
        preg_match("/([Receiver]{8}).+/", strip_tags($html->find(".fa-user", 0)->parent->parent->outerHtml), $status);

        $data = [
            "status"    => true,
            "code"      => ResponseInterface::HTTP_OK,
            "ringkasan" => [
                "no_resi"       => $no_resi,
                "kurir"         => "JNE Express",
                "layanan"       => trim($html->find(".x_content h2", 0)->text),
                "status"        => isset($status[1]) ? $status[1] : "",
                "tanggal_kirim" => date("Y-m-d H:i:s", strtotime(trim($html->find(".tile h4 b", 0)->text))),
                "deskripsi"     => trim($html->find(".tile h4 b", 3)->text),
                "berat"         => trim($html->find(".tile h4 b", 2)->text),
            ],
            "detail"    => [
                "kota_asal"   => trim($html->find(".tile h4 b", 5)->text),
                "kota_tujuan" => trim($html->find(".tile h4 b", 7)->text),
                "pengirim"    => trim($html->find(".tile h4 b", 4)->text),
                "penerima"    => trim($html->find(".tile h4 b", 6)->text),
                "keterangan"  => isset($status[0]) ? trim($status[0]) : "",
            ],
        ];

        $data['history'] = [];
        foreach ($html->find("li") as $konten) {
            $cari    = $this->dom->loadStr($konten->innerHtml);
            $history = [
                "deskripsi" => trim($cari->find('h2 a', 0)->text),
                "tanggal"   => date("Y-m-d H:i:s", strtotime(trim($cari->find('.byline span', 0)->text))),
            ];
            array_push($data['history'], $history);
        }

        return $this->response->setJSON($data)->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
