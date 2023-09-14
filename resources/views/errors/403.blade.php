@extends('errors::layout')

@section('title', 'Forbidden')

@section('message', $exception->getMessage()?:'权限不足！')
