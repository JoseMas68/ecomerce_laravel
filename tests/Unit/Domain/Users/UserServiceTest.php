<?php

namespace Tests\Unit\Domain\Users;

use Tests\TestCase;
use App\Domain\Users\Models\Address;
use App\Domain\Users\Services\UserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
        Storage::fake('public');
    }

    /**
     * Test updating user profile successfully.
     */
    public function test_update_profile(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $updateData = [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ];

        // Act
        $updatedUser = $this->userService->updateProfile($user, $updateData);

        // Assert
        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('new@example.com', $updatedUser->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    /**
     * Test updating profile validates email uniqueness.
     */
    public function test_update_profile_validates_email(): void
    {
        // Arrange
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // Expect exception - email already exists
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Act
        $this->userService->updateProfile($user1, [
            'email' => 'user2@example.com',
        ]);
    }

    /**
     * Test updating profile with partial data.
     */
    public function test_update_profile_with_partial_data(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        // Act - Update only name
        $updatedUser = $this->userService->updateProfile($user, [
            'name' => 'Updated Name',
        ]);

        // Assert
        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('original@example.com', $updatedUser->email);
    }

    /**
     * Test changing user password successfully.
     */
    public function test_change_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);

        // Act
        $this->userService->changePassword($user, 'new_secure_password');

        // Assert
        $this->assertTrue(Hash::check('new_secure_password', $user->fresh()->password));
        $this->assertFalse(Hash::check('old_password', $user->fresh()->password));
    }

    /**
     * Test changing password revokes all tokens.
     */
    public function test_change_password_revokes_tokens(): void
    {
        // Arrange
        $user = User::factory()->create();
        // Create some tokens
        $user->tokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test_token'),
            'abilities' => ['*'],
        ]);

        $initialTokenCount = $user->tokens()->count();

        // Act
        $this->userService->changePassword($user, 'new_password');

        // Assert
        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }

    /**
     * Test changing password hashes the new password.
     */
    public function test_change_password_hashes_new_password(): void
    {
        // Arrange
        $user = User::factory()->create();
        $plainPassword = 'plain_text_password';

        // Act
        $this->userService->changePassword($user, $plainPassword);

        // Assert
        $this->assertNotEquals($plainPassword, $user->fresh()->password);
        $this->assertTrue(Hash::check($plainPassword, $user->fresh()->password));
    }

    /**
     * Test creating address for user successfully.
     */
    public function test_create_address(): void
    {
        // Arrange
        $user = User::factory()->create();
        $addressData = [
            'label' => 'Home',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main Street',
            'city' => 'Madrid',
            'postal_code' => '28001',
            'province' => 'Madrid',
            'phone' => '+34600123456',
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ];

        // Act
        $address = $this->userService->createAddress($user, $addressData);

        // Assert
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals($user->id, $address->user_id);
        $this->assertEquals('Home', $address->label);
        $this->assertEquals('John', $address->first_name);
        $this->assertEquals('123 Main Street', $address->address_line_1);
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'label' => 'Home',
            'city' => 'Madrid',
        ]);
    }

    /**
     * Test creating address sets default if it's the first address.
     */
    public function test_create_address_sets_default_if_first(): void
    {
        // Arrange
        $user = User::factory()->create();
        $addressData = [
            'label' => 'Home',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main Street',
            'city' => 'Madrid',
            'postal_code' => '28001',
            'province' => 'Madrid',
            'phone' => '+34600123456',
            'is_default_shipping' => true,
            'is_default_billing' => false,
        ];

        // Act
        $address = $this->userService->createAddress($user, $addressData);

        // Assert
        $this->assertTrue($address->is_default_shipping);
    }

    /**
     * Test creating address sets default shipping removes default from other addresses.
     */
    public function test_create_address_sets_default_shipping_removes_from_others(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Create existing default address
        $existingAddress = Address::factory()->forUser($user)->defaultShipping()->create();

        $addressData = [
            'label' => 'Work',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address_line_1' => '456 Work Avenue',
            'city' => 'Barcelona',
            'postal_code' => '08001',
            'province' => 'Barcelona',
            'phone' => '+34600987654',
            'is_default_shipping' => true,
            'is_default_billing' => false,
        ];

        // Act
        $newAddress = $this->userService->createAddress($user, $addressData);

        // Assert
        $this->assertTrue($newAddress->is_default_shipping);
        $this->assertFalse($existingAddress->fresh()->is_default_shipping);
    }

    /**
     * Test creating address sets default billing removes default from other addresses.
     */
    public function test_create_address_sets_default_billing_removes_from_others(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Create existing default address
        $existingAddress = Address::factory()->forUser($user)->defaultBilling()->create();

        $addressData = [
            'label' => 'Work',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address_line_1' => '456 Work Avenue',
            'city' => 'Barcelona',
            'postal_code' => '08001',
            'province' => 'Barcelona',
            'phone' => '+34600987654',
            'is_default_shipping' => false,
            'is_default_billing' => true,
        ];

        // Act
        $newAddress = $this->userService->createAddress($user, $addressData);

        // Assert
        $this->assertTrue($newAddress->is_default_billing);
        $this->assertFalse($existingAddress->fresh()->is_default_billing);
    }

    /**
     * Test updating existing address successfully.
     */
    public function test_update_address(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address = Address::factory()->forUser($user)->create([
            'city' => 'Madrid',
            'postal_code' => '28001',
        ]);

        $updateData = [
            'city' => 'Barcelona',
            'postal_code' => '08001',
        ];

        // Act
        $updatedAddress = $this->userService->updateAddress($address, $updateData);

        // Assert
        $this->assertEquals('Barcelona', $updatedAddress->city);
        $this->assertEquals('08001', $updatedAddress->postal_code);
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'city' => 'Barcelona',
            'postal_code' => '08001',
        ]);
    }

    /**
     * Test updating address to default shipping removes default from others.
     */
    public function test_update_address_to_default_shipping_removes_from_others(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address1 = Address::factory()->forUser($user)->defaultShipping()->create();
        $address2 = Address::factory()->forUser($user)->create();

        // Act
        $this->userService->updateAddress($address2, [
            'is_default_shipping' => true,
        ]);

        // Assert
        $this->assertTrue($address2->fresh()->is_default_shipping);
        $this->assertFalse($address1->fresh()->is_default_shipping);
    }

    /**
     * Test deleting address successfully.
     */
    public function test_delete_address(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address = Address::factory()->forUser($user)->create();

        // Act
        $this->userService->deleteAddress($address);

        // Assert
        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
        ]);
    }

    /**
     * Test setting default shipping address.
     */
    public function test_set_default_shipping_address(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address1 = Address::factory()->forUser($user)->defaultShipping()->create();
        $address2 = Address::factory()->forUser($user)->create();

        // Act
        $updatedAddress = $this->userService->setDefaultAddress($user, $address2, 'shipping');

        // Assert
        $this->assertTrue($updatedAddress->is_default_shipping);
        $this->assertFalse($address1->fresh()->is_default_shipping);
    }

    /**
     * Test setting default billing address.
     */
    public function test_set_default_billing_address(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address1 = Address::factory()->forUser($user)->defaultBilling()->create();
        $address2 = Address::factory()->forUser($user)->create();

        // Act
        $updatedAddress = $this->userService->setDefaultAddress($user, $address2, 'billing');

        // Assert
        $this->assertTrue($updatedAddress->is_default_billing);
        $this->assertFalse($address1->fresh()->is_default_billing);
    }

    /**
     * Test getting user addresses ordered by default flags.
     */
    public function test_get_user_addresses(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address1 = Address::factory()->forUser($user)->create(['label' => 'Address 1']);
        $address2 = Address::factory()->forUser($user)->defaultShipping()->create(['label' => 'Address 2']);
        $address3 = Address::factory()->forUser($user)->create(['label' => 'Address 3']);

        // Act
        $addresses = $this->userService->getUserAddresses($user);

        // Assert
        $this->assertCount(3, $addresses);
        // Default addresses should come first
        $this->assertEquals('Address 2', $addresses->first()->label);
    }

    /**
     * Test getting user statistics with orders.
     */
    public function test_get_user_stats(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Create some orders
        \App\Domain\Orders\Models\Order::factory()->count(2)->forUser($user)->pending()->create();
        \App\Domain\Orders\Models\Order::factory()->count(3)->forUser($user)->delivered()
            ->paid()
            ->create(['total' => 100.00]);

        Address::factory()->count(2)->forUser($user)->create();

        // Act
        $stats = $this->userService->getUserStats($user);

        // Assert
        $this->assertEquals(5, $stats['total_orders']);
        $this->assertEquals(2, $stats['pending_orders']); // pending + processing
        $this->assertEquals(3, $stats['completed_orders']);
        $this->assertEquals('300.00', $stats['total_spent']);
        $this->assertEquals('100.00', $stats['average_order_value']);
        $this->assertEquals(2, $stats['address_count']);
        $this->assertNotNull($stats['last_order_date']);
        $this->assertNotNull($stats['member_since']);
    }

    /**
     * Test getting user stats for new user with no orders.
     */
    public function test_get_user_stats_for_new_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $stats = $this->userService->getUserStats($user);

        // Assert
        $this->assertEquals(0, $stats['total_orders']);
        $this->assertEquals(0, $stats['completed_orders']);
        $this->assertEquals(0, $stats['pending_orders']);
        $this->assertEquals('0.00', $stats['total_spent']);
        $this->assertEquals('0.00', $stats['average_order_value']);
        $this->assertEquals(0, $stats['address_count']);
        $this->assertNull($stats['last_order_date']);
    }

    /**
     * Test upload avatar successfully.
     */
    public function test_upload_avatar(): void
    {
        // Arrange
        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg');

        // Act
        $url = $this->userService->uploadAvatar($user, $file);

        // Assert
        $this->assertIsString($url);
        $this->assertStringContainsString('avatars', $url);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    /**
     * Test upload avatar replaces existing avatar.
     */
    public function test_upload_avatar_replaces_existing(): void
    {
        // Arrange
        $user = User::factory()->create(['avatar' => 'avatars/old_avatar.jpg']);
        Storage::disk('public')->put('avatars/old_avatar.jpg', 'old content');

        $newFile = \Illuminate\Http\UploadedFile::fake()->image('new_avatar.jpg');

        // Act
        $url = $this->userService->uploadAvatar($user, $newFile);

        // Assert
        Storage::disk('public')->assertMissing('avatars/old_avatar.jpg');
        Storage::disk('public')->exists($user->fresh()->avatar);
    }

    /**
     * Test delete account removes user and related data.
     */
    public function test_delete_account(): void
    {
        // Arrange
        $user = User::factory()->create();
        Address::factory()->count(2)->forUser($user)->create();
        $user->tokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test_token'),
            'abilities' => ['*'],
        ]);

        // Act
        $this->userService->deleteAccount($user);

        // Assert
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('addresses', ['user_id' => $user->id]);
        $this->assertEquals(0, \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->count());
    }

    /**
     * Test delete account with avatar removes the avatar file.
     */
    public function test_delete_account_with_avatar_removes_file(): void
    {
        // Arrange
        $user = User::factory()->create(['avatar' => 'avatars/test_avatar.jpg']);
        Storage::disk('public')->put('avatars/test_avatar.jpg', 'test content');

        // Act
        $this->userService->deleteAccount($user);

        // Assert
        Storage::disk('public')->assertMissing('avatars/test_avatar.jpg');
    }

    /**
     * Test create address with optional fields.
     */
    public function test_create_address_with_optional_fields(): void
    {
        // Arrange
        $user = User::factory()->create();
        $addressData = [
            'label' => 'Office',
            'first_name' => 'Carlos',
            'last_name' => 'Garcia',
            'address_line_1' => '789 Office Blvd',
            'address_line_2' => 'Suite 100',
            'company' => 'Tech Company',
            'city' => 'Valencia',
            'postal_code' => '46001',
            'province' => 'Valencia',
            'phone' => '+34600555555',
        ];

        // Act
        $address = $this->userService->createAddress($user, $addressData);

        // Assert
        $this->assertEquals('Suite 100', $address->address_line_2);
        $this->assertEquals('Tech Company', $address->company);
        $this->assertDatabaseHas('addresses', [
            'address_line_2' => 'Suite 100',
            'company' => 'Tech Company',
        ]);
    }

    /**
     * Test update address with both default flags.
     */
    public function test_update_address_with_both_default_flags(): void
    {
        // Arrange
        $user = User::factory()->create();
        $existingShipping = Address::factory()->forUser($user)->defaultShipping()->create();
        $existingBilling = Address::factory()->forUser($user)->defaultBilling()->create();
        $address = Address::factory()->forUser($user)->create();

        // Act
        $this->userService->updateAddress($address, [
            'is_default_shipping' => true,
            'is_default_billing' => true,
        ]);

        // Assert
        $this->assertTrue($address->fresh()->is_default_shipping);
        $this->assertTrue($address->fresh()->is_default_billing);
        $this->assertFalse($existingShipping->fresh()->is_default_shipping);
        $this->assertFalse($existingBilling->fresh()->is_default_billing);
    }
}
