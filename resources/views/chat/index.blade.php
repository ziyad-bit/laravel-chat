@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">

            <div class="col-4">
                <div class="list-group" id="list-tab" role="tablist">
                    @foreach ($users as $i => $user)

                        <a class="user{{ $user->id }} list-group-item list-group-item-action {{ $i == 0 ? 'active' : null }}"
                            id="list-home-list" data-toggle="list" href={{ '#c' . $i }} role="tab"
                            data-id="{{ $user->id }}" aria-controls="home"
                            data-index="{{ $i }}">{{ $user->name }} </a>

                    @endforeach
                </div>
            </div>

            <div class="col-8">

                <div class="tab-content" id="nav-tabContent">
                    @foreach ($users as $i => $user)

                        <div class="tab-pane fade  {{ $i == 0 ? 'show active' : null }}" id={{ 'c' . $i }}
                            role="tabpanel" aria-labelledby="list-home-list{{ $i }}">
                            <form id={{ 'form' . $user->id }}>
                                @csrf
                                <div class="card">
                                    <h5 class="card-header">chat<span id="loading{{$user->id}}"
                                            style="margin-left: 50px;display:none">loading old messages</span></h5>

                                    <div class="card-body n" id="receiver{{ $user->id }}">


                                    </div>
                                    <input type="hidden" name="receiver_id" class="receiver_id"
                                        value="{{ $user->id }}">
                                    <input type="text" name="msg" id="msg{{ $user->id }}" class="form-control" data-id="{{ $user->id }}">
                                    <button type="button" class="btn btn-success" data-id="{{ $user->id }}">Send</button>
                                </div>
                            </form>
                        </div>


                    @endforeach
                </div>

            </div>
        </div>

    </div>
@endsection

@section('script')
    <script>
        "use strict"
        //get messages for first user
        $(function() {
            let receiver_id = $("[data-index='0']").data('id');

            $.ajax({
                type   : "get",
                url    : "/chat/" + receiver_id,
                success: function(response, status, xhr) {
                    if (xhr.status == 200) {
                        let messages = response.messages
                        for (let i = 0; i < messages.length; i++) {
                            $('#receiver' + receiver_id).prepend(
                                '<h4>' + messages[i].users.name + '</h4>' +
                                '<P data-message-id="' + messages[i].id + '" class="msg_p">' +
                                messages[i].message + '</P>'
                            );
                        }

                        document.querySelector('.card-body').scrollTo({
                            top     : 10000,
                            left    : 0,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        })

        //get messages for other users
        let users = document.getElementsByClassName('list-group-item');
        for (let i = 0; i < users.length; i++) {
            users[i].onclick = function(e) {
                let receiver_id = e.target.getAttribute('data-id'),
                    user_elem   = document.querySelector('.user' + receiver_id);

                document.querySelector('.receiver_id').value = receiver_id;

                if (user_elem.classList.contains('dis')) {
                    return;
                }

                fetch("/chat/" + receiver_id, {
                    method: 'get',
                }).then(function(res) {
                    return res.json()
                }).then(function(data) {
                    let messages = data.messages;

                    for (let i = 0; i < messages.length; i++) {
                        document.querySelector('#receiver' + receiver_id)
                            .insertAdjacentHTML('afterbegin',
                                '<h4>' + messages[i].users.name + '</h4>' +
                                '<P data-message-id="' + messages[i].id + '" class="msg_p">' + messages[i]
                                .message + '</P>'
                            )
                    }

                    document.querySelector('.card-body').scrollTo({
                        top     : 10000,
                        left    : 0,
                        behavior: 'smooth'
                    });

                    user_elem.classList.add('dis');
                });

                //load old messages for other users
                let chat_card = document.getElementsByClassName('card-body')
                for (let i = 0; i < chat_card.length; i++) {
                    chat_card[i].onscroll = function() {
                        if (chat_card[i].scrollTop == 0) {
                            let load_elem    = document.getElementById('loading'+receiver_id)
                            let first_msg_id = document.querySelector('#receiver' + receiver_id + ' .msg_p')
                                .getAttribute('data-message-id')

                            if (user_elem.classList.contains('end')) {
                                return;
                            }
                            load_elem.style.display = '';

                            axios.put('chat/' + receiver_id, {
                                    'first_msg_id': first_msg_id
                                })
                                .then(function(res) {
                                    let messages = res.data.messages;

                                    for (let i = 0; i < messages.length; i++) {
                                        document.querySelector('#receiver' + receiver_id)
                                            .insertAdjacentHTML('afterbegin',
                                                '<h4>' + messages[i].users.name + '</h4>' +
                                                '<P data-message-id="' + messages[i].id + '" class="msg_p">' +
                                                messages[i]
                                                .message + '</P>'
                                            )
                                    }

                                    chat_card[i].scrollTo({
                                        top     : 30,
                                        left    : 0,
                                        behavior: 'smooth'
                                    });
                                }).catch(function(err) {
                                    user_elem.classList.add('end')
                                    console.log(err.response.data)
                                })
                        }
                    }

                }


            }
        }

        //load old messages for first user
        let chat_card          = document.querySelector('.card-body')
            chat_card.onscroll = function() {

            if (chat_card.scrollTop == 0) {
                let receiver_id = document.querySelector('a[data-index="0"]')
                    .getAttribute('data-id'),
                    user_elem = document.querySelector('.user' + receiver_id);

                let load_elem    = document.getElementById('loading'+receiver_id),
                    first_msg_id = document.getElementsByClassName('msg_p')[0]
                    .getAttribute('data-message-id');

                if (user_elem.classList.contains('end')) {
                    return;
                }

                load_elem.style.display = ''

                fetch('chat/' + receiver_id, {
                    method : 'put',
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-Token": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        'first_msg_id': first_msg_id
                    })
                }).then(function(res) {
                    if (res.ok) {
                        return res.json()
                    }

                    if (res.status == 404) {
                        user_elem.classList.add('end');
                    }

                    return res.json().then(err => Promise.reject(err));
                }).then(function(data) {
                    let messages = data.messages;

                    for (let i = 0; i < messages.length; i++) {
                        document.querySelector('#receiver' + receiver_id)
                            .insertAdjacentHTML('afterbegin',
                                '<h4>' + messages[i].users.name + '</h4>' +
                                '<P data-message-id="' + messages[i].id + '" class="msg_p" id="' + messages[i]
                                .id + '">' + messages[i].message + '</P>'
                            )
                    }

                    load_elem.style.display = 'none'

                    document.querySelector('.card-body').scrollTo({
                        top     : 30,
                        left    : 0,
                        behavior: 'smooth'
                    });
                }).catch(function(err) {
                    load_elem.style.display = 'none'

                    console.log(err.error)
                })
            }
        }

        //store messages and send them
        function storeMessages(e){
            e.preventDefault()
            let id       = e.target.getAttribute('data-id')
            let formData = new FormData($('#form' + id)[0])

            $.ajax({
                type       : "post",
                url        : "{{ route('chat.store') }}",
                data       : formData,
                processData: false,
                contentType: false,
                success    : function(response) {
                    let msg = $('#msg' + id).val();

                    $('#receiver' + id)
                        .append('<h4>' + '{{ Auth::user()->name }}' + '</h4>' + '<P >' + msg +
                            '</P>');

                    $('input[name="msg"]').val('');
                    document.querySelector('.card-body').scrollTo({
                        top     : 10000,
                        left    : 0,
                        behavior: 'smooth'
                    });
                }
            });
        }

        $('input[name="msg"]').on('keypress', function (e) {
            if (e.keyCode == 13) {
                storeMessages(e)
            }
            
        });

        
        $('.btn-success').on('click', function(e) {
            storeMessages(e)
        });

        //subscribe channel

        Echo.private('chat.' + '{{ Auth::id() }}')
            .listen('Message', (e) => {

                $('#receiver' + e.sender_id)
                    .append('<h4>' + e.user_name + '</h4>' + '<P>' + e.message + '</P>');

            });
    </script>
@endsection
