# Declarative

Simple Puppet style declarative programming implementation in PHP.

This library lets you define self-managing resource types in standard PHP, then chain them together with any other
resources they depend on. Resources will only be executed if they need to be.

These properties make it ideal for writing installers and bootstrap scripts that need to reliably create a known-good state from
a system in an unknown state.

## Types
Types are the building blocks - they manage a certain resource for you. You declare the state you want the resource to be
in, and it is their job to make sure the state on the system is the one you want.

Built in types:

`FileSystem::`
 * `ensure_file` - Creates / deletes files and manages contents
 * `ensure_file_line` - Adds or removes lines in files
 * `ensure_directory` Creates or deletes directories
 
## Author

Doug Fitzmaurice (dig412)

# Test
