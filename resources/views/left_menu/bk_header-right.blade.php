<li class="dropdown messages-menu">
    <mail-notifications url="{{ url('/') }}" {{Sentinel::getUser()->inRole('customer')?'prefix=/customers':'prefix='}}></mail-notifications>
</li>

<li class="dropdown notifications-menu">
    <notifications url="{{ url('/') }}"></notifications>
</li>

<li class="dropdown user user-menu">
    <a href="#" class="dropdown-toggle padding-user" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        @if($user_data->user_avatar)
            <img src="{!! url('/').'/uploads/avatar/'.$user_data->user_avatar !!}" alt="img"
                 class="img-circle img-responsive pull-left" height="35" width="35"/>
        @else
            <img src="{{ url('uploads/avatar/user.png') }}" alt="img"
                 class="img-circle img-responsive pull-left" height="35" width="35"/>
        @endif
        <div class="riot">
            <div>
                <p class="user_name_max">{{$user_data->full_name}}</p>
                <i class="fa fa-caret-down" aria-hidden="true"></i>
            </div>
        </div>
    </a>
    <ul class="dropdown-menu">

        <li class="user-name text-center">
            <p class="name_para">{{ $user_data->full_name }}</p>
        </li>
        <!-- Menu Body -->
        <li class="p-t-3">

            <a href="{{url('profile')}}" class="text-primary">

                {{trans('My Profile')}}
                <i class="fa fa-fw fa-user pull-right "></i>
            </a>
        </li>

    <!-- Menu Footer-->
        <li class="p-t-3">
            <a href="{{ url('logout') }}" class="text-danger">

                {{trans('left_menu.logout')}}
                <i class="fa fa-fw fa-sign-out pull-right"></i>

            </a>
        </li>
    </ul>
</li>
