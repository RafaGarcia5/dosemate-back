<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DoseTrack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DoseTrackController extends Controller
{
    public function createTrack(Request $request){
        $validator = Validator::make($request->all(), [
            'medicament_id' => 'required|exists:medicament,id',
            'schedule' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if (DoseTrack::existsDuplicate($request->medicament_id, $request->schedule)) {
            return response()->json(['error' => __('dose-track.duplicate_track')], 409);
        }

        $track = DoseTrack::create([
            'medicament_id' => $request->medicament_id,
            'schedule' => $request->schedule,
            'taken_dose' => $request->taken_dose ?? false,
            'taken_time' => $request->taken_time,
        ]);

        return response()->json(['message' => __('dose-track.create_track_success')], 201);
    }

    public function ById($id){
        $track = DoseTrack::find($id);
        return $track ? response()->json($track) : response()->json(['error' => __('track_not_found')], 404);
    }

    public function byMedicament($medicament_id){
        $tracks = DoseTrack::where('medicament_id', $medicament_id)->get();
        return $tracks->isNotEmpty() ? response()->json($tracks) : response()->json(['error' => __('dose-track.tracks_not_found')], 404);
    }

    public function bySchedule(Request $request){
        $schedule = $request->query('schedule');
        $user = Auth::user();

        $tracks = DoseTrack::findByScheduleWithRelations($user->id, $schedule);

        return $tracks->isNotEmpty() ? response()->json($tracks) : response()->json(['error' => __('dose-track.tracks_not_found')], 404);
    }

    public function update(Request $request, $id){
        $track = DoseTrack::find($id);
        if (!$track) return response()->json(['error' => __('dose-track.track_not_found')], 404);

        $validator = Validator::make($request->all(), [
            'schedule' => 'sometimes',
            'taken_dose' => 'sometimes',
            'taken_time' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $track->update($validated);

        return response()->json(['message' => __('dose-track.update_track_success')], 200);
    }

    public function delete($id){
        $track = DoseTrack::find($id);
        if (!$track) return response()->json(['error' => __('dose-track.track_not_found')], 404);

        $track->delete();
        return response()->json(['message' => __('dose-track.delete_track_success')], 200);
    }

    public function deleteByMedicament($medicament_id){
        $deleted = DoseTrack::where('medicament_id', $medicament_id)->delete();
        return $deleted ? response()->json(['message' => __('dose-track.delete_track_list')], 200) : response()->json(['error' => __('dose-track.tracks_not_found')], 404);
    }
}
