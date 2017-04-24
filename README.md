
## CAS Logout

Per the CAS Protocol, the `/logout` endpoint is responsible for destroying the current SSO session. Upon logout,
it may also be desirable to redirect back to a service. This is controlled via specifying the redirect link via
the service parameter.

The redirect behavior is turned off by default, and is activated via the following setting in `cas.properties`:

    # Specify whether CAS should redirect to the specified service parameter on /logout requests
    # cas.logout.followServiceRedirects=false

The specified url must be registered in the service registry of CAS and enabled.
