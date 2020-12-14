<?php
/**
 * @package Core
 * @subpackage model.enum
 */

interface AmazonS3FilesStorageClassType extends BaseEnum
{
	const STORAGE_CLASS_STANDARD = 'STANDARD';
	const STORAGE_CLASS_REDUCED_REDUNDANCY = 'REDUCED_REDUNDANCY';
	const STORAGE_CLASS_STANDARD_IA = 'STANDARD_IA';
	const STORAGE_CLASS_ONEZONE_IA = 'ONEZONE_IA';
	const STORAGE_CLASS_INTELLIGENT_TIERING = 'INTELLIGENT_TIERING';
	const STORAGE_CLASS_GLACIER = 'GLACIER';
	const STORAGE_CLASS_DEEP_ARCHIVE = 'DEEP_ARCHIVE';
}