<?php

namespace Inspirum\Balikobot\Tests\Unit\Client\Request;

use Inspirum\Balikobot\Exceptions\BadRequestException;
use Inspirum\Balikobot\Services\Client;
use Inspirum\Balikobot\Tests\Unit\Client\AbstractClientTestCase;

class AddRequestTest extends AbstractClientTestCase
{
    public function testThrowsExceptionOnError()
    {
        $this->expectException(BadRequestException::class);

        $requester = $this->newRequesterWithMockedRequestMethod(400, [
            'status' => 200,
        ]);

        $client = new Client($requester);

        $client->addPackages('cp', [['eid' => 1]]);
    }

    public function testRequestShouldHaveStatus()
    {
        $this->expectException(BadRequestException::class);

        $requester = $this->newRequesterWithMockedRequestMethod(200, []);

        $client = new Client($requester);

        $client->addPackages('cp', [['eid' => 1]]);
    }

    public function testThrowsExceptionOnBadStatusCode()
    {
        $this->expectException(BadRequestException::class);

        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status' => 400,
            0        => [
                'carrier_id' => 'NP1504102246M',
                'package_id' => '42719',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                'status'     => '200',
            ],
        ]);

        $client = new Client($requester);

        $client->addPackages('cp', [['eid' => 1]]);
    }

    public function testThrowsExceptionWhenNoReturnPackages()
    {
        $this->expectException(BadRequestException::class);

        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status' => 200,
            0        => [
                'status' => 200,
            ],
        ]);

        $client = new Client($requester);

        $client->addPackages('cp', [['eid' => 1]]);
    }

    public function testThrowsExceptionWhenBadResponseData()
    {
        $this->expectException(BadRequestException::class);

        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status' => 200,
            0        => [
                'package_id' => '42719',
                'status'     => 200,
            ],
            1        => [
                'status' => 200,
            ],
        ]);

        $client = new Client($requester);

        $client->addPackages('cp', [['eid' => 1], ['eid' => 2]]);
    }

    public function testThrowsExceptionWhenWrongNumberOfPackages()
    {
        $this->expectException(BadRequestException::class);

        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status' => 200,
            0        => [
                'carrier_id' => 'NP1504102246M',
                'package_id' => '42719',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                'status'     => '200',
            ],
            1        => [
                'carrier_id' => 'NP1504102247M',
                'package_id' => '42720',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                'status'     => '200',
            ],
        ]);

        $client = new Client($requester);

        $client->addPackages('cp', [['eid' => 1]]);
    }

    public function testMakeRequest()
    {
        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status' => 200,
            0        => [
                'carrier_id' => 'NP1504102246M',
                'package_id' => '42719',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                'status'     => '200',
            ],
        ]);

        $client = new Client($requester);

        $client->addPackages('cp', [['data' => [1, 2, 3], 'test' => false]]);

        $requester->shouldHaveReceived(
            'request',
            ['https://apiv2.balikobot.cz/cp/add', [['data' => [1, 2, 3], 'test' => false]]]
        );

        $this->assertTrue(true);
    }

    public function testOnlyPackagesDataAreReturned()
    {
        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status'     => 200,
            'labels_url' => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
            0            => [
                'carrier_id' => 'NP1504102246M',
                'package_id' => '42719',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                'status'     => '200',
            ],
            1            => [
                'carrier_id' => 'NP1504102247M',
                'package_id' => '42720',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoB.',
                'status'     => '200',
            ],
        ]);

        $client = new Client($requester);

        $packages = $client->addPackages('cp', [['eid' => '0001'], ['eid' => '0002']]);

        $this->assertEquals(
            [
                0 => [
                    'carrier_id' => 'NP1504102246M',
                    'package_id' => '42719',
                    'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                    'status'     => '200',
                ],
                1 => [
                    'carrier_id' => 'NP1504102247M',
                    'package_id' => '42720',
                    'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoB.',
                    'status'     => '200',
                ],
            ],
            $packages
        );
    }

    public function testLabelsUrl()
    {
        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status'     => 200,
            'labels_url' => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoC.',
            0            => [
                'carrier_id' => 'NP1504102246M',
                'package_id' => '42719',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                'status'     => '200',
            ],
            1            => [
                'carrier_id' => 'NP1504102247M',
                'package_id' => '42720',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoB.',
                'status'     => '200',
            ],
        ]);

        $client    = new Client($requester);
        $labelsUrl = null;

        $client->addPackages('cp', [['eid' => '0001'], ['eid' => '0002']], null, $labelsUrl);

        $this->assertEquals('https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoC.', $labelsUrl);
    }

    public function testMakeV2Request()
    {
        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status' => 200,
            0        => [
                'carrier_id' => 'NP1504102246M',
                'package_id' => '42719',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                'status'     => '200',
            ],
        ]);

        $client = new Client($requester);

        $client->addPackages('ups', [['data' => [1, 2, 3], 'test' => false]], 'v2');

        $requester->shouldHaveReceived(
            'request',
            ['https://apiv2.balikobot.cz/v2/ups/add', [['data' => [1, 2, 3], 'test' => false]]]
        );

        $this->assertTrue(true);
    }

    public function testMakeRequestWithUnsupportedVersion()
    {
        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status' => 200,
            0        => [
                'carrier_id' => 'NP1504102246M',
                'package_id' => '42719',
                'label_url'  => 'https://pdf.balikobot.cz/cp/eNorMTIwt9A1NbYwMwdcMBAZAoA.',
                'status'     => '200',
            ],
        ]);

        $client = new Client($requester);

        $client->addPackages('cp', [['data' => [1, 2, 3], 'test' => false]], 'v4');

        $requester->shouldHaveReceived(
            'request',
            ['https://apiv2.balikobot.cz/cp/add', [['data' => [1, 2, 3], 'test' => false]]]
        );

        $this->assertTrue(true);
    }
}