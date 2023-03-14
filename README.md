# Smithsonian Open Access Drupal Module

The Smithsonian Open Access module provides a simple and convenient way to interact with the Smithsonian Open Access API in your Drupal site. It allows you to search the API, customize the search results using various parameters, and display the JSON data on the screen.

## Features

- Easy configuration of API settings through the Drupal admin interface
- Test API key functionality to verify the validity of the API key
- A search form to test search queries using the Smithsonian Open Access API
- An API wrapper class (`Api.php`) that can be used by other developers to interact with the API

## Installation

1. Download the `smithsonian_open_access` module and place it in the `/modules/custom` directory of your Drupal site.
2. Enable the module using the Drupal admin interface or by running `drush en smithsonian_open_access` in the command line.

## Configuration

1. Navigate to the module configuration page at `/admin/config/smithsonian-open-access`.
2. Enter your Smithsonian Open Access API key and customize other settings if necessary.
3. Click the "Test API Key" button to verify the validity of your API key.
4. Save the configuration.

## Usage

To use the search form provided by the module, navigate to `/admin/config/content/smithsonian-open-access/search-test`. Enter a search phrase and click the "Search" button to display the JSON data returned by the API.

To use the API wrapper class in your custom code, you can follow these steps:

1. Import the `Api` class: `use Drupal\smithsonian_open_access\Api;`
2. Inject the `Api` class as a dependency in your custom class or service.
3. Call the `search()` method of the `Api` class to perform a search query.

Refer to the module's source code for more examples and details on using the API wrapper class.

## API Documentation

The `smithsonian_open_access.api.yml` file in the module's root directory provides an OpenAPI specification for the module's API. You can use this file to generate API documentation or provide it to other developers for easier integration.

## Contributing

If you'd like to contribute to the project, please submit an issue or pull request on the project's GitHub repository.

## License

This module is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).
