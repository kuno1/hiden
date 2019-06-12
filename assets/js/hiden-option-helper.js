/**
 * Option helper.
 */

/*global hoge: true*/


const $ = jQuery;

$( document ).ready( () => {
  const api_key    = $( '#hiden_api_key' ).val();
  const $container = $( '#hiden_api_key_result' );
  if ( ! api_key.length ) {
      $container.removeClass( 'loading' ).addClass( 'invalid' );
      return;
  }
  wp.apiRequest( {
    path: '/hiden/v1/validator'
  } ).done( response => {
    $container.addClass( 'valid' ).find( 'strong' ).text( response.name );
  }).fail( response => {
    $container.addClass( 'invalid' );
  }).always( () => {
    $container.removeClass( 'loading' );
  } );
});
