@extends('errors.layout')

@section('title', '500 — Error del servidor')
@section('bar-color', 'bg-gradient-to-r from-rose-500 to-red-500')
@section('code-color', 'text-rose-500')
@section('code', '500')
@section('heading', 'Error interno del servidor')
@section('message', 'Algo salió mal en el servidor. El equipo técnico ha sido notificado. Por favor intenta nuevamente en unos minutos.')
