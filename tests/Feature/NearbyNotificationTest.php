<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use App\Notifications\NearbyPetReportNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NearbyNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscriber_near_a_new_report_is_notified(): void
    {
        Notification::fake();

        $near = Subscriber::create([
            'name' => 'Vale',
            'email' => 'vale@example.com',
            'latitude' => 4.6584,
            'longitude' => -74.0932,
            'radius_km' => 5,
        ]);

        $far = Subscriber::create([
            'name' => 'Lejano',
            'email' => 'lejano@example.com',
            'latitude' => 4.9,
            'longitude' => -74.9,
            'radius_km' => 5,
        ]);

        $response = $this->post('/reportar', [
            'type' => 'perdida',
            'description' => 'Perrito café perdido cerca del parque',
            'contact_phone' => '3001234567',
            'latitude' => 4.66,
            'longitude' => -74.09,
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($near, NearbyPetReportNotification::class);
        Notification::assertNotSentTo($far, NearbyPetReportNotification::class);
    }

    public function test_subscriber_with_notifications_disabled_is_not_notified(): void
    {
        Notification::fake();

        $subscriber = Subscriber::create([
            'name' => 'Vale',
            'email' => 'vale@example.com',
            'latitude' => 4.6584,
            'longitude' => -74.0932,
            'radius_km' => 5,
        ]);
        $subscriber->notifications_enabled = false;
        $subscriber->save();

        $this->post('/reportar', [
            'type' => 'encontrada',
            'description' => 'Gato encontrado cerca del parque',
            'contact_phone' => '3001234567',
            'latitude' => 4.66,
            'longitude' => -74.09,
        ]);

        Notification::assertNotSentTo($subscriber, NearbyPetReportNotification::class);
    }

    public function test_notification_links_directly_to_the_report_detail(): void
    {
        Notification::fake();

        $subscriber = Subscriber::create([
            'name' => 'Vale',
            'email' => 'vale@example.com',
            'latitude' => 4.6584,
            'longitude' => -74.0932,
            'radius_km' => 5,
        ]);

        $this->post('/reportar', [
            'type' => 'perdida',
            'description' => 'Perrito café perdido cerca del parque',
            'contact_phone' => '3001234567',
            'latitude' => 4.66,
            'longitude' => -74.09,
        ]);

        Notification::assertSentTo($subscriber, function (NearbyPetReportNotification $notification) {
            $mail = $notification->toMail($notification);

            return str_contains($mail->actionUrl, '/reporte/'.$notification->report->management_token);
        });
    }
}
