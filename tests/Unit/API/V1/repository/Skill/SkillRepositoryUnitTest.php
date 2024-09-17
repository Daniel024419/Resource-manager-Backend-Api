<?php

namespace Tests\Unit\API\V1\repository\Skill;

use App\Models\V1\Employee\Employee;
use App\Models\V1\skill\Skill;
use App\Repository\V1\Skills\SkillsRepository;
use Database\Factories\V1\Skill\SkillFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillRepositoryUnitTest extends TestCase
{
    use RefreshDatabase;

    private $skillRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->skillRepository = new SkillsRepository();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test fetching all skills from the repository.
     */
    public function testfetch()
    {
        // Create two skills
        $skill1 = $this->createSkill();
        $skill2 = $this->createSkill();

        // Fetch all skills
        $allSkills = $this->skillRepository->fetch();

        // Assert that both skill names are present in the result
        $this->assertContains($skill1->name, $allSkills->pluck('name'));
        $this->assertContains($skill2->name, $allSkills->pluck('name'));
    }

    /**
     * Test saving new skills to the repository.
     */
    public function testsave()
    {
        // Generate data for a new skill using the factory
        $newSkillData = SkillFactory::new()->make()->toArray();

        // Call the method to save the new skill
        $savedSkill = $this->skillRepository->save($newSkillData);

        // Check if the skill was saved successfully
        $this->assertInstanceOf(Skill::class, $savedSkill);

        // Check if the skill is present in the database
        $this->assertDatabaseHas('skills', [
            'id' => $savedSkill['id'],
            'name' => $savedSkill['name'],
            'employee_id' => $newSkillData['employee_id'],
        ]);
    }

    /**
     * Test updating skills by ID in the repository.
     */
    public function testupdateById()
    {
        // Create a skill using the factory
        $originalSkill = $this->createSkill();

        // Generate updated data for the skill
        $updatedSkillData = [
            'id' => $originalSkill->id,
            'name' => 'hacking ethically',
        ];

        // Call the method to update the skill
        $updatedSkillsCount = $this->skillRepository->updateById($updatedSkillData);

        // Check if the skill was updated successfully
        $this->assertTrue($updatedSkillsCount > 0);

        // Check if the skill in the database has the updated data
        $this->assertDatabaseHas('skills', $updatedSkillData);
    }

    /**
     * Test deleting skills by ID in the repository.
     */
    public function testDeleteSkillById()
    {
        $skill = $this->createSkill();

        $deleteSkill = $this->skillRepository->deleteById($skill->id);

        $this->assertTrue($deleteSkill);
    }

    /**
     * Test storing skills by name in the repository.
     */
    public function teststoreByName()
    {
        // Create an employee using the factory
        $employee = Employee::factory()->create();

        // Call the method to store a skill by name for the employee
        $result = $this->skillRepository->storeByName('New Skill', $employee->id);

        // Check if the skill was stored successfully
        $this->assertTrue($result);

        // Check if the skill is present in the database
        $this->assertDatabaseHas('skills', ['name' => 'New Skill', 'employee_id' => $employee->id]);
    }

    /**
     * Test updating user skills by name in the repository.
     */
    public function testUpdateUserSkillsByName()
    {
        $skill = $this->createSkill();

        $newSkillName = 'Kunfu black holder';

        // Call the method to update user skills by name for the employee
        $result = $this->skillRepository->updateUserSkillsByName($newSkillName, $skill->employee_id);

        // Check if the user skills were updated successfully
        $this->assertTrue($result);

        // Check if the skills are present in the database with the updated information
        $this->assertDatabaseHas('skills', ['name' => $newSkillName, 'employee_id' => $skill->employee_id]);
        $this->assertDatabaseMissing('skills', ['name' => 'Old Skill', 'employee_id' => $skill->employee_id]);
    }

    /**
     * Helper method to create a skill.
     *
     * @return Skill
     */
    private function createSkill(): Skill
    {
        return Skill::factory()->create();
    }
}