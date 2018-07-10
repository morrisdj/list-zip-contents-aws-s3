# list-zip-contents-aws-s3
##Lists the contents of a zip archive that is stored in Amazon (AWS) S3 without downloading the entire zip file

####1. Credentials for the AWS SDK for PHP Version 3
To make requests to Amazon Web Services, you must supply AWS access keys, also known as credentials, to the AWS SDK for PHP.

You can do this in the following ways:

- Use the default credential provider chain (recommended).
- Use a specific credential provider or provider chain (or create your own).
- Supply the credentials yourself. These can be root account credentials, IAM credentials, or temporary credentials retrieved from AWS STS.

**Important**

For security, AWS strongly recommends that you use IAM users instead of the root account for AWS access. For more information, see IAM Best Practices in the IAM User Guide.

###3. Instantiate the class using the factory method:

Once you have created a credential provider, pass it to the factory method to get an instance of the ListZip class:
```PHP
$listZip = ListZip::create($provider);
```

###4. Call `getFiles` to retrieve file list
Pass the bucket and the zip file name.
```PHP
$files = $listZip->getFiles($zipfilename, $bucket);
```

File list is an array of FileItems (see FileItem.php).
