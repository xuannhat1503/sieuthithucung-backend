package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.UserDto;
import com.sieuthithucung.dto.UserRegisterDto;
import com.sieuthithucung.entity.UserEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.UserMapper;
import com.sieuthithucung.repository.UserRepository;
import com.sieuthithucung.service.UserService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.time.LocalDateTime;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class UserServiceImpl implements UserService {

    private final UserRepository repository;
    private final BCryptPasswordEncoder passwordEncoder;

    public UserServiceImpl(UserRepository repository, BCryptPasswordEncoder passwordEncoder) {
        this.repository = repository;
        this.passwordEncoder = passwordEncoder;
    }

    @Override
    public UserDto create(UserDto dto) {
        return sanitize(createInternal(dto));
    }

    @Override
    public UserDto update(Long id, UserDto dto) {
        return sanitize(updateInternal(id, dto));
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("User not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public UserDto findById(Long id) {
        return sanitize(findByIdInternal(id));
    }

    @Override
    public List<UserDto> getAll() {
        return getAllInternal().stream().map(this::sanitize).toList();
    }

    @Override
    public UserDto register(UserRegisterDto registerDto) {
        if (registerDto == null || isBlank(registerDto.getName()) || 
            isBlank(registerDto.getEmail()) || isBlank(registerDto.getPassword())) {
            throw new IllegalArgumentException("Name, email, and password are required");
        }

        if (repository.findByEmail(registerDto.getEmail().trim()).isPresent()) {
            throw new IllegalArgumentException("Email da duoc dang ky");
        }

        UserEntity entity = new UserEntity();
        entity.setName(registerDto.getName().trim());
        entity.setEmail(registerDto.getEmail().trim());
        entity.setPassword(passwordEncoder.encode(registerDto.getPassword()));
        entity.setPhoneNumber(registerDto.getPhoneNumber());
        entity.setAddress(registerDto.getAddress());
        entity.setStatus("active");
        entity.setRoleId(3L);
        entity.setCreatedAt(LocalDateTime.now());

        UserEntity saved = repository.save(entity);
        return sanitize(UserMapper.mapToUserDto(saved));
    }

    private UserDto createInternal(UserDto dto) {
        UserEntity entity = UserMapper.mapToUserEntity(dto);
        UserEntity saved = repository.save(entity);
        return UserMapper.mapToUserDto(saved);
    }

    private UserDto updateInternal(Long id, UserDto dto) {
        UserEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("User not found with id: " + id));

        UserEntity source = UserMapper.mapToUserEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        UserEntity saved = repository.save(existing);
        return UserMapper.mapToUserDto(saved);
    }

    private UserDto findByIdInternal(Long id) {
        UserEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("User not found with id: " + id));
        return UserMapper.mapToUserDto(entity);
    }

    private List<UserDto> getAllInternal() {
        return repository.findAll().stream().map(UserMapper::mapToUserDto).toList();
    }

    private UserDto sanitize(UserDto dto) {
        if (dto != null) {
            dto.setPassword(null);
        }
        return dto;
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }

    private boolean isBlank(String value) {
        return value == null || value.trim().isEmpty();
    }
}

