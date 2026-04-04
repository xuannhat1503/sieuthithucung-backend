package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.UserDto;
import com.sieuthithucung.entity.UserEntity;
import com.sieuthithucung.mapper.UserMapper;
import com.sieuthithucung.repository.UserRepository;
import org.springframework.stereotype.Service;

@Service
public class UserService extends AbstractCrudService<UserEntity, Long, UserDto> {

    public UserService(UserRepository repository, UserMapper mapper) {
        super(repository, mapper, "User");
    }
}

