<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use App\Notifications\NearbyPetReportNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NearbyNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscriber_near_a_new_report_is_notified(): void
    {
        Notification::fake();
        Storage::fake('public');

        $near = $this->subscriberWithZone('vale@example.com', 4.6584, -74.0932, 5);

        $far = $this->subscriberWithZone('lejano@example.com', 4.9, -74.9, 5);

        $response = $this->post('/reportar', $this->payload('perdida', 'Perrito café perdido cerca del parque'));

        $response->assertRedirect();

        Notification::assertSentTo($near, NearbyPetReportNotification::class);
        Notification::assertNotSentTo($far, NearbyPetReportNotification::class);
    }

    public function test_subscriber_with_notifications_disabled_is_not_notified(): void
    {
        Notification::fake();
        Storage::fake('public');

        $subscriber = $this->subscriberWithZone('vale@example.com', 4.6584, -74.0932, 5);
        $subscriber->zones()->update(['active' => false]);

        $this->post('/reportar', $this->payload('encontrada', 'Gato encontrado cerca del parque'));

        Notification::assertNotSentTo($subscriber, NearbyPetReportNotification::class);
    }

    public function test_notification_links_directly_to_the_report_detail(): void
    {
        Notification::fake();
        Storage::fake('public');

        $subscriber = $this->subscriberWithZone('vale@example.com', 4.6584, -74.0932, 5);

        $this->post('/reportar', $this->payload('perdida', 'Perrito café perdido cerca del parque'));

        Notification::assertSentTo($subscriber, function (NearbyPetReportNotification $notification) {
            $mail = $notification->toMail($notification);

            // El aviso lleva al enlace PÚBLICO del reporte, nunca al enlace privado de gestión.
            return str_contains($mail->actionUrl, '/reportes/'.$notification->report->id)
                && ! str_contains($mail->actionUrl, $notification->report->management_token);
        });
    }

    private function subscriberWithZone(string $email, float $lat, float $lng, int $radius): Subscriber
    {
        $subscriber = Subscriber::create(['name' => 'Vale', 'email' => $email]);
        $subscriber->zones()->create(['label' => 'Casa', 'latitude' => $lat, 'longitude' => $lng, 'radius_km' => $radius]);

        return $subscriber;
    }

    private function payload(string $type, string $description): array
    {
        return [
            'type' => $type,
            'species' => 'perro',
            'breed' => 'Criollo / mestizo',
            'sex' => 'macho',
            'size' => 'mediano',
            'colors' => ['cafe'],
            'event_date' => now()->toDateString(),
            'description' => $description,
            'photo' => UploadedFile::fake()->image('perro.jpg'),
            'contact_phone' => '3001234567',
            'latitude' => 4.66,
            'longitude' => -74.09,
        ];
    }
}
