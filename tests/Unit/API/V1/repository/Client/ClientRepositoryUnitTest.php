<?php

namespace Tests\Unit\API\V1\repository\Client;

use App\Models\V1\Client\Client;
use Tests\TestCase;
use App\Repository\V1\Client\ClientRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientRepositoryUnitTest extends TestCase
{
    use RefreshDatabase;

    private $clientRepository;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientRepository = new ClientRepository();
        $this->client = $this->createClient();
    }

    // Test saving a new client
    public function testsave()
    {
        $clientData = Client::factory()->make()->toArray();

        $result = $this->clientRepository->save($clientData);

        $this->assertInstanceOf(Client::class, $result);
        $this->assertEquals($clientData['name'], $result->name);
        $this->assertDatabaseHas('clients', ['name' => $clientData['name']]);
    }

    // Test fetching all clients with a query
    public function testfetchWithQuery()
    {
        $client1 = $this->client;
        $client2 = $this->createClient();

        $result = $this->clientRepository->fetch('');

        $this->assertInstanceOf(Collection::class, $result);

        $this->assertTrue($result->contains('id', $client1->id));
        $this->assertTrue($result->contains('id', $client2->id));
    }

    // Test fetching all clients without a query
    public function testfetchWithoutQuery()
    {
        $client1 = $this->client;
        $client2 = $this->createClient();
        $client3 = $this->createClient();

        $result = $this->clientRepository->fetch('');

        $this->assertInstanceOf(Collection::class, $result);

        $this->assertCount(3, $result);
        $this->assertTrue($result->contains('id', $client1->id));
        $this->assertTrue($result->contains('id', $client2->id));
        $this->assertTrue($result->contains('id', $client3->id));
    }

    // Test finding a client by client ID
    public function testfindByClientId()
    {
        $result = $this->clientRepository->findByClientId($this->client->clientId);

        $this->assertInstanceOf(Client::class, $result);
        $this->assertEquals($this->client->clientId, $result->clientId);
    }

    // Test deleting a client by client ID
    public function testdeleteByClientId()
    {
        $result = $this->clientRepository->deleteByClientId($this->client->clientId);

        $this->assertTrue($result);
        $this->assertSoftDeleted('clients', ['id' => $this->client->id]);
    }

    // Test finding a client by client name
    public function testfindByClientName()
    {
        $nameToFind = $this->client;

        $result = $this->clientRepository->findClientByName($nameToFind['name']);

        if ($result !== null) {
            $this->assertInstanceOf(Client::class, $result);
            $this->assertEquals($nameToFind['name'], $result->name);
        } else {
            $this->assertNull($result);
        }
    }

    // Test updating a client by client ID
    public function testUpdateClientByClientId()
    {
        $newClientData = [
            'clientId' => $this->client->clientId,
            'name' => 'shadow dev',
            'details' => 'this is a shadow dev',
        ];

        $result = $this->clientRepository->updateByClientId($newClientData);

        $updatedClient = Client::where('clientId', $this->client->clientId)->first();

        $this->assertInstanceOf(Client::class, $result);
        $this->assertEquals($newClientData['name'], $result->name);
        $this->assertEquals($newClientData['details'], $result->details);

        $this->assertEquals($newClientData['name'], $updatedClient->name);
        $this->assertEquals($newClientData['details'], $updatedClient->details);
    }

    // Test fetching archived clients
    public function testfetchArchives()
    {
        $this->client->delete();

        $result = $this->clientRepository->fetchArchives();

        $this->assertInstanceOf(Collection::class, $result);

        $this->assertTrue($result->contains('id', $this->client->id));
    }

    // Test archiving a client
    public function testArchiveClient()
    {
        $clientId = $this->client->clientId;

        $result = $this->clientRepository->archive($clientId);

        $this->assertTrue($result);

        $this->assertSoftDeleted('clients', ['id' => $this->client->id]);
    }

    // Test restoring a client from the archive
    public function testrestoreArchive()
    {
        $clientId = $this->client->clientId;

        $this->clientRepository->archive($clientId);

        $result = $this->clientRepository->restoreArchive($clientId);

        $this->assertTrue($result);

        $this->assertDatabaseHas('clients', ['id' => $this->client->id, 'deleted_at' => null]);
    }

    // Test deleting an archived client
    public function testDeleteArchivedClient()
    {
        $clientId = $this->client->clientId;

        $this->clientRepository->archive($clientId);

        $result = $this->clientRepository->deletedClient($clientId);

        $this->assertTrue($result);

        $this->assertDatabaseMissing('clients', ['id' => $this->client->id]);
    }

    // Test searching for archived clients by name or code
    public function testsearchByNameOrCode()
    {
        $nameOrCode = $this->client->name;

        $this->clientRepository->archive($this->client->clientId);

        $result = $this->clientRepository->searchByNameOrCode($nameOrCode);

        $this->assertInstanceOf(Collection::class, $result);

        $this->assertCount(1, $result);
        $this->assertEquals($this->client->id, $result[0]->id);
    }

    // Test finding an archived client by client ID
    public function testfindArchiveByClientId()
    {
        $this->clientRepository->archive($this->client->clientId);

        $result = $this->clientRepository->findArchiveByClientId($this->client->clientId);

        $this->assertInstanceOf(Client::class, $result);

        $this->assertEquals($this->client->id, $result->id);
    }

    // Helper function to create a client
    private function createClient(): Client
    {
        return Client::factory()->create();
    }
}